<?php

namespace App\Filament\Widgets;

use App\Models\CompanySubscription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product3DModel;
use Filament\Widgets\Widget;

class PlatformInsights extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.platform-insights';

    protected function getViewData(): array
    {
        $validOrders = Order::query()->where('status', '!=', 'cancelled');
        $totalOrders = (clone $validOrders)->count();
        $deliveredOrders = (clone $validOrders)->where('status', 'delivered')->count();
        $deliveryRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0;

        $outstanding = (float) (clone $validOrders)->sum('remaining_amount');
        $monthlyPayments = (float) Payment::query()
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        $activeSubscriptions = CompanySubscription::query()
            ->whereIn('status', ['active', 'expiring'])
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->count();

        $completedModels = Product3DModel::query()->where('status', 'completed')->count();

        return [
            'deliveryRate' => $deliveryRate,
            'outstanding' => $outstanding,
            'monthlyPayments' => $monthlyPayments,
            'activeSubscriptions' => $activeSubscriptions,
            'completedModels' => $completedModels,
            'currency' => config('app.currency', 'SYP'),
        ];
    }
}
