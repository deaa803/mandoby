<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanySubscriptionController extends Controller
{
    public function current(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $subscription = $company->subscriptions()
            ->usable()
            ->with('plan.features')
            ->orderByDesc('end_date')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Current subscription retrieved successfully',
            'data' => [
                'subscription' => $subscription,
                'features' => $company->activeFeatureKeys(),
                'can_use_3d_models' => $company->hasActiveFeature('3d_models'),
                'can_view_advanced_reports' => $company->hasActiveFeature('advanced_reports'),
            ],
        ]);
    }
}
