<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    /*
     * على الهاتف يأخذ العرض كاملًا،
     * وعلى الشاشات المتوسطة والكبيرة يأخذ نصف الصف
     * حتى يمكن وضع Widget آخر بجانبه.
     */
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    /*
     * يمنع المخطط من التمدد عموديًا.
     */
    protected ?string $maxHeight = '270px';

    protected ?string $heading = 'نشاط المنصة';

    protected ?string $description = 'الطلبات والمنتجات وأرباح المنصة خلال آخر 30 يومًا';

    protected function getData(): array
    {
        $dates = collect();

        for ($day = 29; $day >= 0; $day--) {
            $dates->push(
                Carbon::today()
                    ->subDays($day)
                    ->format('Y-m-d')
            );
        }

        $startDate = Carbon::today()
            ->subDays(29)
            ->startOfDay();

        $endDate = Carbon::today()
            ->endOfDay();

        /*
         * عدد الطلبات اليومية مع استبعاد الطلبات الملغاة.
         */
        $ordersData = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        /*
         * عدد المنتجات المضافة يوميًا.
         */
        $productsData = Product::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        /*
         * أرباح المنصة من حقل العمولة الحقيقي.
         */
        $profitData = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                'DATE(created_at) as day, COALESCE(SUM(commission), 0) as total'
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $dates
                        ->map(
                            fn (string $date): int =>
                            (int) ($ordersData[$date] ?? 0)
                        )
                        ->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.10)',
                    'pointBackgroundColor' => '#22c55e',
                    'pointBorderColor' => '#22c55e',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'المنتجات المضافة',
                    'data' => $dates
                        ->map(
                            fn (string $date): int =>
                            (int) ($productsData[$date] ?? 0)
                        )
                        ->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#3b82f6',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'أرباح المنصة',
                    'data' => $dates
                        ->map(
                            fn (string $date): float => round(
                                (float) ($profitData[$date] ?? 0),
                                2
                            )
                        )
                        ->toArray(),
                    'borderColor' => '#c9a227',
                    'backgroundColor' => 'rgba(201, 162, 39, 0.08)',
                    'pointBackgroundColor' => '#c9a227',
                    'pointBorderColor' => '#c9a227',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],

            'labels' => $dates
                ->map(
                    fn (string $date): string =>
                    Carbon::parse($date)->format('d/m')
                )
                ->toArray(),
        ];
    }

    protected function getOptions(): array
    {
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
                    'rtl' => true,
                    'textDirection' => 'rtl',
                    'labels' => [
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                        'boxHeight' => 8,
                        'padding' => 12,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],

                'tooltip' => [
                    'enabled' => true,
                    'rtl' => true,
                    'textDirection' => 'rtl',
                ],
            ],

            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'maxRotation' => 0,
                        'autoSkip' => true,
                        'maxTicksLimit' => 7,
                        'font' => [
                            'size' => 10,
                        ],
                    ],
                ],

                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'drawBorder' => false,
                    ],
                    'ticks' => [
                        'precision' => 0,
                        'font' => [
                            'size' => 10,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
