<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductDetailController extends Controller
{
    private array $relations = [
        'product',
        'company',
        'category',
        'images',
        'features',
        'model3d',
    ];

    /**
     * Display a listing of product details.
     */
    public function index()
    {
        $productDetails = ProductDetail::with($this->relations)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Product details retrieved successfully',
            'data' => $productDetails,
        ]);
    }

    /**
     * Store a newly created product detail.
     *
     * Company accounts do not send company_id from Flutter. The company is resolved
     * from the authenticated Sanctum token.
     */
    public function store(Request $request)
    {
        $company = $request->user()?->company;

        $validated = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'product_name' => ['required_without:product_id', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['nullable', 'in:available,unavailable'],
            'price' => ['nullable', 'numeric', 'min:0'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'features' => ['nullable', 'array'],
            'features.*.feature_id' => ['required_with:features', 'exists:features,id'],
            'features.*.value' => ['required_with:features', 'string', 'max:255'],
        ]);

        $companyId = $company?->id ?? ($validated['company_id'] ?? null);

        if (!$companyId) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        try {
            $productDetail = DB::transaction(function () use ($request, $validated, $companyId) {
                $product = !empty($validated['product_id'])
                    ? Product::findOrFail($validated['product_id'])
                    : Product::create([
                        'name' => $validated['product_name'],
                        'description' => $validated['description'] ?? null,
                        'min_order_quantity' => $validated['min_order_quantity'] ?? 1,
                    ]);

                $productDetail = ProductDetail::create([
                    'product_id' => $product->id,
                    'company_id' => $companyId,
                    'category_id' => $validated['category_id'],
                    'status' => $validated['status'] ?? 'available',
                    'price' => $validated['price'] ?? 0,
                ]);

                $featuresData = [];

                foreach ($validated['features'] ?? [] as $feature) {
                    $featuresData[$feature['feature_id']] = [
                        'value' => $feature['value'],
                    ];
                }

                if (!empty($featuresData)) {
                    $productDetail->features()->attach($featuresData);
                }

                if ($request->hasFile('image')) {
                    $this->storeProductImage($productDetail->id, $request->file('image'));
                }

                foreach ($request->file('images', []) as $imageFile) {
                    $this->storeProductImage($productDetail->id, $imageFile);
                }

                return $productDetail;
            });

            return response()->json([
                'status' => true,
                'message' => 'Product detail created successfully',
                'data' => $productDetail->load($this->relations),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create product detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product detail.
     */
    public function show($id)
    {
        $productDetail = ProductDetail::with($this->relations)->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Product detail retrieved successfully',
            'data' => $productDetail,
        ]);
    }

    /**
     * Update the specified product detail.
     */
    public function update(Request $request, ProductDetail $productDetail)
    {
        $validated = $request->validate([
            'product_id' => ['sometimes', 'required', 'exists:products,id'],
            'product_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'company_id' => ['sometimes', 'required', 'exists:companies,id'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'status' => ['nullable', 'in:available,unavailable'],
            'price' => ['nullable', 'numeric', 'min:0'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'features' => ['nullable', 'array'],
            'features.*.feature_id' => ['required_with:features', 'exists:features,id'],
            'features.*.value' => ['required_with:features', 'string', 'max:255'],
        ]);

        try {
            $updatedProductDetail = DB::transaction(function () use ($request, $validated, $productDetail) {
                $productDetailData = collect($validated)
                    ->only(['product_id', 'company_id', 'category_id', 'status', 'price'])
                    ->toArray();

                if (!empty($productDetailData)) {
                    $productDetail->update($productDetailData);
                }

                if (isset($validated['product_name']) || array_key_exists('description', $validated) || array_key_exists('min_order_quantity', $validated)) {
                    $productData = [];

                    if (isset($validated['product_name'])) {
                        $productData['name'] = $validated['product_name'];
                    }

                    if (array_key_exists('description', $validated)) {
                        $productData['description'] = $validated['description'];
                    }

                    if (array_key_exists('min_order_quantity', $validated)) {
                        $productData['min_order_quantity'] = $validated['min_order_quantity'] ?? 1;
                    }

                    if (!empty($productData)) {
                        $productDetail->product->update($productData);
                    }
                }

                if (array_key_exists('features', $validated)) {
                    $featuresData = [];

                    foreach ($validated['features'] ?? [] as $feature) {
                        $featuresData[$feature['feature_id']] = [
                            'value' => $feature['value'],
                        ];
                    }

                    $productDetail->features()->sync($featuresData);
                }

                if ($request->hasFile('image')) {
                    foreach ($productDetail->images as $image) {
                        if ($image->url) {
                            Storage::disk('public')->delete($image->url);
                        }
                        $image->delete();
                    }

                    $this->storeProductImage($productDetail->id, $request->file('image'));
                }

                foreach ($request->file('images', []) as $imageFile) {
                    $this->storeProductImage($productDetail->id, $imageFile);
                }

                return $productDetail->fresh()->load($this->relations);
            });

            return response()->json([
                'status' => true,
                'message' => 'Product detail updated successfully',
                'data' => $updatedProductDetail,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update product detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product detail.
     */
    public function destroy(ProductDetail $productDetail)
    {
        try {
            DB::transaction(function () use ($productDetail) {
                $productDetail->features()->detach();

                if ($productDetail->model3d) {
                    $productDetail->model3d->delete();
                }

                foreach ($productDetail->images as $image) {
                    if ($image->url) {
                        Storage::disk('public')->delete($image->url);
                    }
                    $image->delete();
                }

                $productDetail->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Product detail deleted successfully',
                'data' => null,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete product detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function myCompanyProducts(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $products = ProductDetail::with($this->relations)
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'My company products retrieved successfully',
            'data' => $products,
        ]);
    }

    private function storeProductImage(int $productDetailId, $imageFile): Image
    {
        $path = $imageFile->store('product-details/images', 'public');

        return Image::create([
            'product_detail_id' => $productDetailId,
            'url' => $path,
        ]);
    }
}
