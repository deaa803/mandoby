<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductDetail;
use Illuminate\Http\Request;

class CompanyDashboardController extends Controller
{
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

        $orders = Order::query()
            ->whereHas('productDetails', function ($query) use ($company) {
                $query->where('product_details.company_id', $company->id);
            });

        return response()->json([
            'status' => true,
            'message' => 'Company dashboard retrieved successfully',
            'data' => [
                'products_count' => ProductDetail::where('company_id', $company->id)->count(),
                'orders_count' => (clone $orders)->count(),
                'pending_orders' => (clone $orders)
                    ->whereIn('status', ['pending', 'preparing'])
                    ->count(),
                'completed_orders' => (clone $orders)
                    ->where('status', 'delivered')
                    ->count(),
                'total_sales' => (float) (clone $orders)->sum('total_price'),
                'total_paid' => (float) (clone $orders)->sum('paid_amount'),
                'total_remaining' => (float) (clone $orders)->sum('remaining_amount'),
            ],
        ]);
    }
}
