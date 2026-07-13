<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Order;
use App\Models\Store;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $companiesCount = Company::query()->count();
        $storesCount = Store::query()->count();

        $ordersQuery = Order::query()->where('status', '!=', 'cancelled');

        $deliveredOrders = (clone $ordersQuery)
            ->where('status', 'delivered')
            ->count();

        $platformProfit = (float) (clone $ordersQuery)->sum('commission');

        $companiesGrowth = $this->calculateMonthlyGrowth(Company::query());
        $storesGrowth = $this->calculateMonthlyGrowth(Store::query());
        $deliveredGrowth = $this->calculateMonthlyGrowth(
            Order::query()->where('status', 'delivered')
        );
        $profitGrowth = $this->calculateMonthlySumGrowth(
            Order::query()->where('status', '!=', 'cancelled'),
            'commission'
        );

        return [
            Stat::make('الشركات المسجلة', number_format($companiesCount))
                ->description($this->growthDescription($companiesGrowth))
                ->descriptionIcon($companiesGrowth >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->icon(Heroicon::BuildingOffice2)
                ->chart($this->getDailyCountTrend(Company::query()))
                ->color($companiesGrowth >= 0 ? 'success' : 'danger')
                ->extraAttributes(['class' => 'platform-stat platform-stat--emerald']),

            Stat::make('المتاجر المسجلة', number_format($storesCount))
                ->description($this->growthDescription($storesGrowth))
                ->descriptionIcon($storesGrowth >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->icon(Heroicon::BuildingStorefront)
                ->chart($this->getDailyCountTrend(Store::query()))
                ->color($storesGrowth >= 0 ? 'info' : 'danger')
                ->extraAttributes(['class' => 'platform-stat platform-stat--blue']),

            Stat::make('الطلبات المكتملة', number_format($deliveredOrders))
                ->description($this->growthDescription($deliveredGrowth))
                ->descriptionIcon($deliveredGrowth >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->icon(Heroicon::CheckCircle)
                ->chart($this->getDailyDeliveredTrend())
                ->color($deliveredGrowth >= 0 ? 'warning' : 'danger')
                ->extraAttributes(['class' => 'platform-stat platform-stat--amber']),

            Stat::make(
                'أرباح المنصة',
                number_format($platformProfit, 2) . ' ' . config('app.currency', 'SYP')
            )
                ->description($this->growthDescription($profitGrowth))
                ->descriptionIcon($profitGrowth >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->icon(Heroicon::Banknotes)
                ->chart($this->getDailyProfitTrend())
                ->color($profitGrowth >= 0 ? 'success' : 'danger')
                ->extraAttributes(['class' => 'platform-stat platform-stat--violet']),
        ];
    }

    protected function calculateMonthlyGrowth(Builder $query): float
    {
        $current = (clone $query)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $previous = (clone $query)
            ->whereBetween('created_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->count();

        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function calculateMonthlySumGrowth(Builder $query, string $column): float
    {
        $current = (float) (clone $query)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum($column);

        $previous = (float) (clone $query)
            ->whereBetween('created_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum($column);

        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function growthDescription(float $growth): string
    {
        $percentage = number_format(abs($growth), 1);

        return match (true) {
            $growth > 0 => "زيادة {$percentage}٪ عن الشهر الماضي",
            $growth < 0 => "انخفاض {$percentage}٪ عن الشهر الماضي",
            default => 'لا يوجد تغيير عن الشهر الماضي',
        };
    }

    protected function getDailyCountTrend(Builder $query): array
    {
        return (clone $query)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): int => (int) $value)
            ->values()
            ->toArray();
    }

    protected function getDailyDeliveredTrend(): array
    {
        return Order::query()
            ->where('status', 'delivered')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): int => (int) $value)
            ->values()
            ->toArray();
    }

    protected function getDailyProfitTrend(): array
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(commission), 0) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->values()
            ->toArray();
    }
}
