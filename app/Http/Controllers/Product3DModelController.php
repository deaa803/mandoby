<?php

namespace App\Http\Controllers;

use App\Models\Product3DModel;
use App\Models\ProductDetail;
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

        $validated = $request->validate([
            'product_detail_id' => ['required', 'exists:product_details,id'],
            'source_image' => ['required_without:image', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'metadata' => ['nullable', 'array'],
        ]);

        $productDetail = ProductDetail::where('id', $validated['product_detail_id'])
            ->where('company_id', $company->id)
            ->first();

        if (!$productDetail) {
            return response()->json([
                'status' => false,
                'message' => 'The selected product does not belong to this company',
                'data' => null,
            ], 403);
        }

        if ($productDetail->model3d()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This product already has a 3D model record',
                'data' => $productDetail->model3d()->first(),
            ], 422);
        }

        $sourceImage = $request->file('source_image') ?? $request->file('image');
        $sourceImagePath = $sourceImage->store('product-3d/source-images', 'public');

        $model = Product3DModel::create([
            'product_detail_id' => $productDetail->id,
            'company_id' => $company->id,
            'source_image' => $sourceImagePath,
            'status' => 'pending',
            'progress' => 0,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => '3D model request created successfully',
            'data' => $model->load($this->relations),
        ], 201);
    }

    public function show(Request $request, Product3DModel $model3d)
    {
        if (!$this->belongsToAuthenticatedCompany($request, $model3d)) {
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

    public function update(Request $request, Product3DModel $model3d)
    {
        if (!$this->belongsToAuthenticatedCompany($request, $model3d)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to update this 3D model',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,processing,completed,failed'],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'error_message' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'model_file' => ['nullable', 'file', 'max:102400'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'source_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($request->hasFile('model_file')) {
            $this->validateModelExtension($request);

            if ($model3d->model_file) {
                Storage::disk('public')->delete($model3d->model_file);
            }

            $validated['model_file'] = $request->file('model_file')
                ->store('product-3d/models', 'public');

            $validated['status'] = 'completed';
            $validated['progress'] = 100;
            $validated['generated_at'] = now();
            $validated['error_message'] = null;
        }

        if ($request->hasFile('thumbnail')) {
            if ($model3d->thumbnail) {
                Storage::disk('public')->delete($model3d->thumbnail);
            }

            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('product-3d/thumbnails', 'public');
        }

        if ($request->hasFile('source_image')) {
            if ($model3d->source_image) {
                Storage::disk('public')->delete($model3d->source_image);
            }

            $validated['source_image'] = $request->file('source_image')
                ->store('product-3d/source-images', 'public');

            if ($model3d->model_file) {
                Storage::disk('public')->delete($model3d->model_file);
                $validated['model_file'] = null;
            }

            if ($model3d->thumbnail) {
                Storage::disk('public')->delete($model3d->thumbnail);
                $validated['thumbnail'] = null;
            }

            if (!$request->filled('status')) {
                $validated['status'] = 'pending';
                $validated['progress'] = 0;
                $validated['generated_at'] = null;
                $validated['error_message'] = null;
            }
        }

        if (($validated['status'] ?? null) === 'failed' && !array_key_exists('progress', $validated)) {
            $validated['progress'] = 0;
        }

        $model3d->update($validated);

        return response()->json([
            'status' => true,
            'message' => '3D model updated successfully',
            'data' => $model3d->fresh()->load($this->relations),
        ]);
    }

    public function destroy(Request $request, Product3DModel $model3d)
    {
        if (!$this->belongsToAuthenticatedCompany($request, $model3d)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to delete this 3D model',
                'data' => null,
            ], 403);
        }

        $model3d->delete();

        return response()->json([
            'status' => true,
            'message' => '3D model deleted successfully',
            'data' => null,
        ]);
    }

    public function forProduct(ProductDetail $productDetail)
    {
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

    private function belongsToAuthenticatedCompany(Request $request, Product3DModel $model): bool
    {
        $company = $request->user()?->company;

        return $company && $model->company_id === $company->id;
    }

    private function validateModelExtension(Request $request): void
    {
        $allowedExtensions = ['glb', 'gltf', 'obj', 'fbx', 'zip'];
        $extension = strtolower($request->file('model_file')->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'model_file' => ['The model file must be one of: glb, gltf, obj, fbx, zip.'],
            ]);
        }
    }
}
