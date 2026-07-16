<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected ?string $maxHeight = '275px';

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return app()->getLocale() === 'en'
            ? 'Platform activity'
            : 'نشاط المنصة';
    }

    public function getDescription(): ?string
    {
        return app()->getLocale() === 'en'
            ? 'Orders and platform profit during the last 30 days'
            : 'الطلبات وأرباح المنصة خلال آخر 30 يومًا';
    }

    protected function getData(): array
    {
        $dates = collect();

        for ($day = 29; $day >= 0; $day--) {
            $dates->push(Carbon::today()->subDays($day)->format('Y-m-d'));
        }

        $startDate = Carbon::today()->subDays(29)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $ordersData = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $profitData = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(commission), 0) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $isArabic = app()->getLocale() !== 'en';

        return [
            'datasets' => [
                [
                    'label' => $isArabic ? 'عدد الطلبات' : 'Orders',
                    'data' => $dates
                        ->map(fn (string $date): int => (int) ($ordersData[$date] ?? 0))
                        ->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.12)',
                    'pointBackgroundColor' => '#22c55e',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => $isArabic ? 'أرباح المنصة' : 'Platform profit',
                    'data' => $dates
                        ->map(fn (string $date): float => round((float) ($profitData[$date] ?? 0), 2))
                        ->toArray(),
                    'borderColor' => '#d2ad2e',
                    'backgroundColor' => 'rgba(210, 173, 46, 0.10)',
                    'pointBackgroundColor' => '#d2ad2e',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $dates
                ->map(fn (string $date): string => Carbon::parse($date)->format('d/m'))
                ->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        $isArabic = app()->getLocale() !== 'en';

        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'rtl' => $isArabic,
                    'textDirection' => $isArabic ? 'rtl' : 'ltr',
                    'labels' => [
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                        'boxHeight' => 8,
                        'padding' => 12,
                        'font' => ['size' => 11],
                    ],
                ],
                'tooltip' => [
                    'rtl' => $isArabic,
                    'textDirection' => $isArabic ? 'rtl' : 'ltr',
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => [
                        'maxRotation' => 0,
                        'autoSkip' => true,
                        'maxTicksLimit' => 7,
                        'font' => ['size' => 10],
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'position' => $isArabic ? 'right' : 'left',
                    'ticks' => ['precision' => 0, 'font' => ['size' => 10]],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => $isArabic ? 'left' : 'right',
                    'grid' => ['drawOnChartArea' => false],
                    'ticks' => ['font' => ['size' => 10]],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
