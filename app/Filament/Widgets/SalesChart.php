<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'نشاط المنصة';

    protected function getData(): array
    {
        // آخر 30 يوم
        $dates = collect();

        for ($i = 29; $i >= 0; $i--) {
            $dates->push(Carbon::today()->subDays($i)->format('Y-m-d'));
        }

        // عدد الطلبات يومياً
        $ordersData = Order::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereIn(\DB::raw('DATE(created_at)'), $dates->toArray())
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // عدد المنتجات المضافة يومياً
        $productsData = Product::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereIn(\DB::raw('DATE(created_at)'), $dates->toArray())
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // أرباح التطبيق (3% من إجمالي المبيعات يومياً)
        $profitData = Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(total_price) * 0.03 as total')
            ->whereIn(\DB::raw('DATE(created_at)'), $dates->toArray())
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $dates->map(fn ($date) => $ordersData[$date] ?? 0)->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'المنتجات المضافة',
                    'data' => $dates->map(fn ($date) => $productsData[$date] ?? 0)->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'أرباح التطبيق',
                    'data' => $dates->map(fn ($date) => round($profitData[$date] ?? 0, 2))->toArray(),
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.15)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#8b5cf6',
                ],
            ],

            'labels' => $dates->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
