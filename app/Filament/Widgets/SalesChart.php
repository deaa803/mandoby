<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PlatformProfitService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'حركة الطلبات وأرباح الدفعات';

    protected ?string $description = 'مقارنة يومية خلال آخر 30 يومًا';

    protected function getData(): array
    {
        $dates = collect();

        for ($day = 29; $day >= 0; $day--) {
            $dates->push(Carbon::today()->subDays($day)->format('Y-m-d'));
        }

        $startDate = Carbon::today()->subDays(29)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $orders = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'day')
            ->toArray();

        $rate = app(PlatformProfitService::class)->rate();

        $profits = Payment::query()
            ->whereHas('order', fn (Builder $query) => $query->where('status', '!=', 'cancelled'))
            ->where(function (Builder $query) use ($startDate, $endDate): void {
                $query->whereBetween('paid_at', [$startDate, $endDate])
                    ->orWhere(function (Builder $query) use ($startDate, $endDate): void {
                        $query->whereNull('paid_at')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->selectRaw('DATE(COALESCE(paid_at, created_at)) as day')
            ->selectRaw('ROUND(COALESCE(SUM(amount), 0) * ?, 2) as total', [$rate])
            ->groupBy(DB::raw('DATE(COALESCE(paid_at, created_at))'))
            ->pluck('total', 'day')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $dates->map(fn (string $date): int => (int) ($orders[$date] ?? 0))->toArray(),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, .13)',
                    'pointBackgroundColor' => '#16a34a',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2.5,
                    'tension' => .42,
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'أرباح المنصة من الدفعات',
                    'data' => $dates->map(fn (string $date): float => round((float) ($profits[$date] ?? 0), 2))->toArray(),
                    'borderColor' => '#d4a817',
                    'backgroundColor' => 'rgba(212, 168, 23, .10)',
                    'pointBackgroundColor' => '#d4a817',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2.5,
                    'tension' => .42,
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $dates->map(fn (string $date): string => Carbon::parse($date)->format('d/m'))->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'rtl' => true,
                    'textDirection' => 'rtl',
                    'labels' => [
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                        'boxHeight' => 8,
                        'padding' => 16,
                        'font' => ['size' => 11],
                    ],
                ],
                'tooltip' => ['rtl' => true, 'textDirection' => 'rtl'],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['maxRotation' => 0, 'autoSkip' => true, 'maxTicksLimit' => 7],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'ticks' => ['precision' => 0],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
