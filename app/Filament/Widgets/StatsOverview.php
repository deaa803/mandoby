<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product3DModel;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalRevenue = (float) Order::sum('total_price');
        $totalPayments = (float) Payment::sum('amount');
        $appProfit = (float) Order::sum('commission');

        return [
            Stat::make('الشركات', Company::count())
                ->description('إجمالي الشركات في النظام')
                ->color('primary'),

            Stat::make('المتاجر', Store::count())
                ->description('إجمالي المتاجر في النظام')
                ->color('info'),

            Stat::make('الطلبات', Order::count())
                ->description('إجمالي الطلبات')
                ->color('info'),

            Stat::make('إجمالي الطلبات', number_format($totalRevenue, 2))
                ->description('مجموع قيمة الطلبات')
                ->chart($this->getRevenueTrend())
                ->color('success'),

            Stat::make('المدفوعات', number_format($totalPayments, 2))
                ->description('إجمالي الدفعات المسجلة')
                ->chart($this->getPaymentTrend())
                ->color('warning'),

            Stat::make('عمولة المنصة', number_format($appProfit, 2))
                ->description('مجموع حقل العمولة في الطلبات')
                ->chart($this->getProfitTrend())
                ->color('success'),

            Stat::make('موديلات 3D المكتملة', Product3DModel::where('status', 'completed')->count())
                ->description('الموديلات الجاهزة للعرض')
                ->color('success'),
        ];
    }

    protected function getRevenueTrend(): array
    {
        return Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(total_price) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->toArray();
    }

    protected function getPaymentTrend(): array
    {
        return Payment::query()
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->toArray();
    }

    protected function getProfitTrend(): array
    {
        return Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(commission) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->toArray();
    }
}
