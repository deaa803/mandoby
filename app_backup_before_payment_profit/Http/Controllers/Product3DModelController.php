<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateProduct3DModelJob;
use App\Models\Product3DModel;
use App\Models\ProductDetail;
use App\Services\Product3DModelNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Product3DModelController extends Controller
{
    private array $relations = [
        'productDetail.product',
        'productDetail.category',
        'productDetail.images',
        'company',
    ];

    public function index(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $models = Product3DModel::with($this->relations)
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => '3D models retrieved successfully',
            'data' => $models,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | التحقق من تفعيل خدمة 3D للشركة
        |--------------------------------------------------------------------------
        */

        if (!$company->hasActiveFeature('3d_models')) {
            return response()->json([
                'status' => false,
                'message' => '3D model generation requires an active subscription',
                'data' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | التحقق من البيانات المرسلة
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'product_detail_id' => [
                'required',
                'integer',
                'exists:product_details,id',
            ],

            'source_image' => [
                'required_without:image',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | جلب المنتج والتأكد أنه تابع للشركة
        |--------------------------------------------------------------------------
        */

        $productDetail = ProductDetail::find(
            $validated['product_detail_id']
        );

        if (!$productDetail) {
            return response()->json([
                'status' => false,
                'message' => 'Product detail not found',
                'data' => null,
            ], 404);
        }

        if ((int) $productDetail->company_id !== (int) $company->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to generate a 3D model for this product',
                'data' => null,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | تحديد ملف الصورة
        |--------------------------------------------------------------------------
        |
        | يقبل الـ API اسم الحقل source_image أو image.
        |
        */

        $uploadedImage = $request->file('source_image')
            ?? $request->file('image');

        if (!$uploadedImage) {
            return response()->json([
                'status' => false,
                'message' => 'Source image is required',
                'data' => null,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | فحص وجود عملية سابقة للمنتج
        |--------------------------------------------------------------------------
        */

        $model = Product3DModel::where(
            'product_detail_id',
            $productDetail->id
        )->first();

        /*
        |--------------------------------------------------------------------------
        | منع إنشاء عمليتين بنفس الوقت
        |--------------------------------------------------------------------------
        */

        if (
            $model &&
            in_array($model->status, ['pending', 'processing'], true)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'A 3D model generation process is already running for this product',
                'data' => $model->load($this->relations),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | تخزين صورة المصدر
        |--------------------------------------------------------------------------
        */

        $sourceImagePath = $uploadedImage->store(
            'product-3d/source-images',
            'public'
        );

        /*
        |--------------------------------------------------------------------------
        | إعادة التوليد إذا كان السجل موجوداً
        |--------------------------------------------------------------------------
        */

        if ($model) {
            $oldSourceImage = $model->source_image;
            $oldModelFile = $model->model_file;
            $oldThumbnail = $model->thumbnail;

            $model->update([
                'company_id' => $company->id,
                'product_detail_id' => $productDetail->id,
                'source_image' => $sourceImagePath,
                'model_file' => null,
                'thumbnail' => null,
                'status' => 'pending',
                'progress' => 0,
                'error_message' => null,
                'metadata' => $validated['metadata']
                    ?? $model->metadata,
                'started_at' => null,
                'generated_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | حذف الملفات القديمة
            |--------------------------------------------------------------------------
            */

            if (
                $oldSourceImage &&
                $oldSourceImage !== $sourceImagePath
            ) {
                Storage::disk('public')->delete($oldSourceImage);
            }

            if ($oldModelFile) {
                Storage::disk('public')->delete($oldModelFile);
            }

            if ($oldThumbnail) {
                Storage::disk('public')->delete($oldThumbnail);
            }
        } else {
            /*
            |--------------------------------------------------------------------------
            | إنشاء سجل جديد
            |--------------------------------------------------------------------------
            */

            $model = Product3DModel::create([
                'product_detail_id' => $productDetail->id,
                'company_id' => $company->id,
                'source_image' => $sourceImagePath,
                'model_file' => null,
                'thumbnail' => null,
                'status' => 'pending',
                'progress' => 0,
                'error_message' => null,
                'metadata' => $validated['metadata'] ?? null,
                'started_at' => null,
                'generated_at' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | إرسال عملية التوليد إلى Queue
        |--------------------------------------------------------------------------
        */

        GenerateProduct3DModelJob::dispatch($model->id)
            ->onQueue('3d');

        return response()->json([
            'status' => true,
            'message' => '3D model generation started successfully',
            'data' => $model->fresh()->load($this->relations),
        ], 202);
    }

    public function show(
        Request $request,
        Product3DModel $model3d
    ) {
        if (
            !$this->belongsToAuthenticatedCompany(
                $request,
                $model3d
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to access this 3D model',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => '3D model retrieved successfully',
            'data' => $model3d->load($this->relations),
        ]);
    }

    public function update(
        Request $request,
        Product3DModel $model3d,
        Product3DModelNotificationService $notifier,
    ) {
        if (
            !$this->belongsToAuthenticatedCompany(
                $request,
                $model3d
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to update this 3D model',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'nullable',
                'in:pending,processing,completed,failed',
            ],

            'progress' => [
                'nullable',
                'integer',
                'between:0,100',
            ],

            'error_message' => [
                'nullable',
                'string',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],

            'model_file' => [
                'nullable',
                'file',
                'max:102400',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'source_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        if ($request->hasFile('model_file')) {
            $this->validateModelExtension($request);

            if ($model3d->model_file) {
                Storage::disk('public')->delete(
                    $model3d->model_file
                );
            }

            $validated['model_file'] = $request
                ->file('model_file')
                ->store('product-3d/models', 'public');

            $validated['status'] = 'completed';
            $validated['progress'] = 100;
            $validated['generated_at'] = now();
            $validated['error_message'] = null;
        }

        if ($request->hasFile('thumbnail')) {
            if ($model3d->thumbnail) {
                Storage::disk('public')->delete(
                    $model3d->thumbnail
                );
            }

            $validated['thumbnail'] = $request
                ->file('thumbnail')
                ->store('product-3d/thumbnails', 'public');
        }

        if ($request->hasFile('source_image')) {
            if ($model3d->source_image) {
                Storage::disk('public')->delete(
                    $model3d->source_image
                );
            }

            $validated['source_image'] = $request
                ->file('source_image')
                ->store('product-3d/source-images', 'public');

            if ($model3d->model_file) {
                Storage::disk('public')->delete(
                    $model3d->model_file
                );

                $validated['model_file'] = null;
            }

            if ($model3d->thumbnail) {
                Storage::disk('public')->delete(
                    $model3d->thumbnail
                );

                $validated['thumbnail'] = null;
            }

            if (!$request->filled('status')) {
                $validated['status'] = 'pending';
                $validated['progress'] = 0;
                $validated['started_at'] = null;
                $validated['generated_at'] = null;
                $validated['error_message'] = null;
            }
        }

        if (
            ($validated['status'] ?? null) === 'failed' &&
            !array_key_exists('progress', $validated)
        ) {
            $validated['progress'] = 0;
        }

        $model3d->update($validated);
        $notifier->notifyCompleted($model3d->fresh());

        return response()->json([
            'status' => true,
            'message' => '3D model updated successfully',
            'data' => $model3d
                ->fresh()
                ->load($this->relations),
        ]);
    }

    public function destroy(
        Request $request,
        Product3DModel $model3d
    ) {
        if (
            !$this->belongsToAuthenticatedCompany(
                $request,
                $model3d
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete this 3D model',
                'data' => null,
            ], 403);
        }

        if ($model3d->source_image) {
            Storage::disk('public')->delete(
                $model3d->source_image
            );
        }

        if ($model3d->model_file) {
            Storage::disk('public')->delete(
                $model3d->model_file
            );
        }

        if ($model3d->thumbnail) {
            Storage::disk('public')->delete(
                $model3d->thumbnail
            );
        }

        $model3d->delete();

        return response()->json([
            'status' => true,
            'message' => '3D model deleted successfully',
            'data' => null,
        ]);
    }

    public function forProduct(
        ProductDetail $productDetail
    ) {
        $model = $productDetail->model3d()
            ->where('status', 'completed')
            ->whereNotNull('model_file')
            ->first();

        return response()->json([
            'status' => true,
            'message' => $model
                ? 'Product 3D model retrieved successfully'
                : 'This product does not have a completed 3D model',
            'has_3d_model' => (bool) $model,
            'data' => $model,
        ]);
    }

    public function modelFile(Product3DModel $product3DModel)
    {
        $modelFile = $product3DModel->model_file;

        if (!$modelFile) {
            return response()->json([
                'status' => false,
                'message' => '3D model file is not available',
                'data' => null,
            ], 404);
        }

        $modelFile = ltrim($modelFile, '/');

        if (str_starts_with($modelFile, 'public/')) {
            $modelFile = substr($modelFile, 7);
        }

        if (str_starts_with($modelFile, 'storage/')) {
            $modelFile = substr($modelFile, 8);
        }

        if (!Storage::disk('public')->exists($modelFile)) {
            return response()->json([
                'status' => false,
                'message' => '3D model file was not found in storage',
                'data' => null,
            ], 404);
        }

        $absolutePath = Storage::disk('public')->path($modelFile);

        return response()->file($absolutePath, [
            'Content-Type' => 'model/gltf-binary',
            'Content-Disposition' =>
                'inline; filename="' . basename($absolutePath) . '"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' =>
                'Origin, Content-Type, Accept, Range',
            'Access-Control-Expose-Headers' =>
                'Content-Length, Content-Range, Accept-Ranges',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function belongsToAuthenticatedCompany(
        Request $request,
        Product3DModel $model
    ): bool {
        $company = $request->user()?->company;

        return $company &&
            (int) $model->company_id === (int) $company->id;
    }

    private function validateModelExtension(
        Request $request
    ): void {
        $allowedExtensions = [
            'glb',
            'gltf',
            'obj',
            'fbx',
            'zip',
        ];

        $extension = strtolower(
            $request
                ->file('model_file')
                ->getClientOriginalExtension()
        );

        if (!in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'model_file' => [
                    'The model file must be one of: glb, gltf, obj, fbx, zip.',
                ],
            ]);
        }
    }
}
