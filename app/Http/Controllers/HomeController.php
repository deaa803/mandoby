<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\ProductDetail;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function categories()
    {
        $categories = Category::all();
        return response()->json([
            'status' => true,
            'message' => 'categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    public function specialProducts()
    {
        $specailProducts = ProductDetail::with([
            'product',
            'category',
            'company',
            'images',
            'features'
        ])->latest()
            ->take(10)
            ->get();
        return response()->json([
            'status' => true,
            'message' => 'Special products retrieved successfully',
            'data' => $specailProducts,
        ]);
    }


    public function offers()
    {
       $now= Carbon::now();
        $offers = Advertisement::with([
            'company',
            'productDetails.product',
            'productDetails.category',
            'productDetails.images',
            'productDetails.features'
        ])->where('status','active')->where(function ($query) use ($now) {
            $query->whereDate('advertisements.starts_at', '<=', $now);
        })->where(function ($query) use ($now) {
            $query->whereDate('advertisements.ends_at', '>=', $now);
        })->get();
        return response()->json([
            'status' => true,
            'message' => 'offers retrieved successfully',
            'data' => $offers,
        ]);
    }

    public function productsByCategory($id)
    {
        $products = ProductDetail::with([
            'product',
            'category',
            'company',
            'images',
            'features'
        ])->where('category_id',$id)->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'products retrieved successfully',
            'data' => $products,
        ]);
    }
    public function productsByCompany($id)
    {
        $products = ProductDetail::with([
            'product',
            'category',
            'company',
            'images',
            'features'
        ])->where('company_id',$id)->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'products retrieved successfully',
            'data' => $products,
        ]);
    }
}
