<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\ProductDetail;
use Carbon\Carbon;

class HomeController extends Controller
{
    private array $productDetailRelations = [
        'product',
        'category',
        'company',
        'images',
        'features',
    ];

    public function categories()
    {
        $categories = Category::latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    public function specialProducts()
    {
        $specialProducts = ProductDetail::with($this->productDetailRelations)
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Special products retrieved successfully',
            'data' => $specialProducts,
        ]);
    }

    public function offers()
    {
        $now = Carbon::now();

        $offers = Advertisement::with([
            'company',
            'productDetail.product',
            'productDetail.category',
            'productDetail.images',
            'productDetail.features',
        ])
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Offers retrieved successfully',
            'data' => $offers,
        ]);
    }

    public function productsByCategory($id)
    {
        $products = ProductDetail::with($this->productDetailRelations)
            ->where('category_id', $id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    public function productsByCompany($id)
    {
        $products = ProductDetail::with($this->productDetailRelations)
            ->where('company_id', $id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

}
