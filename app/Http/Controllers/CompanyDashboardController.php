<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductDetail;
use App\Services\PlatformProfitService;
use Illuminate\Http\Request;

class CompanyDashboardController extends Controller
{
    public function index(Request $request, PlatformProfitService $platformProfitService)
    {
        $company = $request->user()?->company;

        if (! $company) {
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

        $subscription = $company->subscriptions()
            ->usable()
            ->with('plan.features')
            ->orderByDesc('end_date')
            ->first();

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
                'platform_account' => $platformProfitService->companyStatement($company, true),
                'subscription' => $subscription,
                'subscription_features' => $company->activeFeatureKeys(),
                'can_use_3d_models' => $company->hasActiveFeature('3d_models'),
                'can_view_advanced_reports' => $company->hasActiveFeature('advanced_reports'),
            ],
        ]);
    }
}
