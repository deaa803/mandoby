<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        $products = Product::with('details')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    /**
     * Store a base product record.
     *
     * Company applications should create products through ProductDetailController.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $product = Product::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product->load('details'),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:5000'],
        ]);

        $product->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => $product->fresh()->load('details'),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
