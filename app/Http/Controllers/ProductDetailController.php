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
     * Create a product and its company-specific details in one request.
     *
     * The company is always resolved from the authenticated Sanctum token.
     * Flutter must not send product_id, company_id, or status when creating.
     */
    public function store(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'This route is available to company accounts only',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'product_id' => ['prohibited'],
            'company_id' => ['prohibited'],
            'status' => ['prohibited'],

            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'min_order_quantity' => ['required', 'integer', 'min:1'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'features' => ['nullable', 'array'],
            'features.*.feature_id' => ['required', 'integer', 'distinct', 'exists:features,id'],
            'features.*.value' => ['required', 'string', 'max:255'],
        ]);

        try {
            $productDetail = DB::transaction(function () use ($request, $validated, $company) {
                $product = Product::create([
                    'name' => $validated['product_name'],
                    'description' => $validated['description'],
                ]);

                $productDetail = ProductDetail::create([
                    'product_id' => $product->id,
                    'company_id' => $company->id,
                    'category_id' => $validated['category_id'],
                    'status' => 'available',
                    'price' => $validated['price'],
                    'min_order_quantity' => $validated['min_order_quantity'],
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
                'message' => 'Product created successfully',
                'data' => $productDetail->load($this->relations),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create product',
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
     * Update a product owned by the authenticated company.
     */
    public function update(Request $request, ProductDetail $productDetail)
    {
        $company = $request->user()?->company;

        if (!$company || $productDetail->company_id !== $company->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to update this product',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'product_id' => ['prohibited'],
            'company_id' => ['prohibited'],

            'product_name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:5000'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'status' => ['sometimes', 'required', 'in:available,unavailable'],
            'price' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'min_order_quantity' => ['sometimes', 'required', 'integer', 'min:1'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'features' => ['sometimes', 'array'],
            'features.*.feature_id' => ['required', 'integer', 'distinct', 'exists:features,id'],
            'features.*.value' => ['required', 'string', 'max:255'],
        ]);

        try {
            $updatedProductDetail = DB::transaction(function () use ($request, $validated, $productDetail) {
                $productDetailData = collect($validated)
                    ->only([
                        'category_id',
                        'status',
                        'price',
                        'min_order_quantity',
                    ])
                    ->toArray();

                if (!empty($productDetailData)) {
                    $productDetail->update($productDetailData);
                }

                $productData = [];

                if (array_key_exists('product_name', $validated)) {
                    $productData['name'] = $validated['product_name'];
                }

                if (array_key_exists('description', $validated)) {
                    $productData['description'] = $validated['description'];
                }

                if (!empty($productData)) {
                    $productDetail->product->update($productData);
                }

                if (array_key_exists('features', $validated)) {
                    $featuresData = [];

                    foreach ($validated['features'] as $feature) {
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
                'message' => 'Product updated successfully',
                'data' => $updatedProductDetail,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a product owned by the authenticated company.
     */
    public function destroy(Request $request, ProductDetail $productDetail)
    {
        $company = $request->user()?->company;

        if (!$company || $productDetail->company_id !== $company->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to delete this product',
                'data' => null,
            ], 403);
        }

        try {
            DB::transaction(function () use ($productDetail) {
                $product = $productDetail->product;

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

                if ($product && !$product->details()->exists()) {
                    $product->delete();
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully',
                'data' => null,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return products owned by the authenticated company.
     */
    public function myCompanyProducts(Request $request)
    {
        $company = $request->user()?->company;

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
