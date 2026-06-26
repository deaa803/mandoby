<?php

namespace App\Http\Controllers;

use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDetailController extends Controller
{
    private array $relations = [
        'product',
        'company',
        'category',
        'images',
        'features',
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
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['nullable', 'string', 'max:255'],

            'features' => ['required', 'array'],
            'features.*.feature_id' => ['required_with:features', 'exists:features,id'],
            'features.*.value' => ['required_with:features', 'string', 'max:255'],
        ]);

        try {
            $productDetail = DB::transaction(function () use ($validated) {
                $productDetail = ProductDetail::create([
                    'product_id' => $validated['product_id'],
                    'company_id' => $validated['company_id'],
                    'category_id' => $validated['category_id'],
                    'status' => $validated['status'] ?? 'available',
                ]);

                $featuresData = [];

                foreach ($validated['features'] as $feature) {
                    $featuresData[$feature['feature_id']] = [
                        'value' => $feature['value'],
                    ];
                }

                $productDetail->features()->attach($featuresData);

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
            'company_id' => ['sometimes', 'required', 'exists:companies,id'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'status' => ['nullable', 'string', 'max:255'],

            'features' => ['nullable', 'array'],
            'features.*.feature_id' => ['required_with:features', 'exists:features,id'],
            'features.*.value' => ['required_with:features', 'string', 'max:255'],
        ]);

        try {
            $updatedProductDetail = DB::transaction(function () use ($validated, $productDetail) {
                $productDetailData = collect($validated)
                    ->only(['product_id', 'company_id', 'category_id', 'status'])
                    ->toArray();

                if (!empty($productDetailData)) {
                    $productDetail->update($productDetailData);
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
}
