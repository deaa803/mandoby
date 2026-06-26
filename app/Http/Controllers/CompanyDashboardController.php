<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductDetail;
use Illuminate\Http\Request;

class CompanyDashboardController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $productsCount = ProductDetail::where('company_id', $company->id)->count();

        $ordersQuery = Order::whereHas('productDetails', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        });

        $ordersCount = (clone $ordersQuery)->count();

        $pendingOrders = (clone $ordersQuery)
            ->where('status', 'pending')
            ->count();

        $completedOrders = (clone $ordersQuery)
            ->where('status', 'completed')
            ->count();

        $totalSales = (clone $ordersQuery)->sum('total_price');

        $totalPaid = (clone $ordersQuery)->sum('paid_amount');

        $totalRemaining = (clone $ordersQuery)->sum('remaining_amount');

        return response()->json([
            'status' => true,
            'message' => 'Company dashboard retrieved successfully',
            'data' => [
                'products_count' => $productsCount,
                'orders_count' => $ordersCount,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
                'total_remaining' => $totalRemaining,
            ],
        ]);
    }
}
