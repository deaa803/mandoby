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

    /**
     * إيقاف التحديث التلقائي لمنع طلبات Livewire المتكررة.
     */
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $isArabic = app()->getLocale() !== 'en';

        $companiesCount = Company::query()->count();
        $storesCount = Store::query()->count();

        $ordersQuery = Order::query()
            ->where('status', '!=', 'cancelled');

        $activeOrdersCount = (clone $ordersQuery)
            ->whereIn('status', ['pending', 'preparing', 'delivering'])
            ->count();

        $platformProfit = (float) (clone $ordersQuery)->sum('commission');

        $companiesGrowth = $this->calculateMonthlyGrowth(Company::query());
        $storesGrowth = $this->calculateMonthlyGrowth(Store::query());
        $ordersGrowth = $this->calculateMonthlyGrowth(
            Order::query()->where('status', '!=', 'cancelled')
        );
        $profitGrowth = $this->calculateMonthlySumGrowth(
            Order::query()->where('status', '!=', 'cancelled'),
            'commission'
        );

        return [
            Stat::make(
                $isArabic ? 'الشركات المسجلة' : 'Registered companies',
                number_format($companiesCount)
            )
                ->description($this->growthDescription($companiesGrowth, $isArabic))
                ->descriptionIcon(
                    $companiesGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::BuildingOffice2)
                ->chart($this->getDailyCountTrend(Company::query()))
                ->color($companiesGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                $isArabic ? 'المتاجر المسجلة' : 'Registered stores',
                number_format($storesCount)
            )
                ->description($this->growthDescription($storesGrowth, $isArabic))
                ->descriptionIcon(
                    $storesGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::BuildingStorefront)
                ->chart($this->getDailyCountTrend(Store::query()))
                ->color($storesGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                $isArabic ? 'الطلبات النشطة' : 'Active orders',
                number_format($activeOrdersCount)
            )
                ->description($this->growthDescription($ordersGrowth, $isArabic))
                ->descriptionIcon(
                    $ordersGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::Truck)
                ->chart($this->getDailyOrderTrend())
                ->color($ordersGrowth >= 0 ? 'info' : 'danger'),

            Stat::make(
                $isArabic ? 'أرباح المنصة' : 'Platform profit',
                number_format($platformProfit, 2) . ' ' . config('app.currency', 'SYP')
            )
                ->description($this->growthDescription($profitGrowth, $isArabic))
                ->descriptionIcon(
                    $profitGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::CurrencyDollar)
                ->chart($this->getDailyProfitTrend())
                ->color($profitGrowth >= 0 ? 'warning' : 'danger'),
        ];
    }

    protected function calculateMonthlyGrowth(Builder $query): float
    {
        $currentMonth = (clone $query)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        $previousMonth = (clone $query)
            ->whereBetween('created_at', [
                Carbon::now()->subMonthNoOverflow()->startOfMonth(),
                Carbon::now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->count();

        if ($previousMonth === 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1);
    }

    protected function calculateMonthlySumGrowth(Builder $query, string $column): float
    {
        $currentMonth = (float) (clone $query)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->sum($column);

        $previousMonth = (float) (clone $query)
            ->whereBetween('created_at', [
                Carbon::now()->subMonthNoOverflow()->startOfMonth(),
                Carbon::now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum($column);

        if ($previousMonth <= 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1);
    }

    protected function growthDescription(float $growth, bool $isArabic): string
    {
        $percentage = number_format(abs($growth), 1);

        if ($growth > 0) {
            return $isArabic
                ? "زيادة {$percentage}٪ عن الشهر الماضي"
                : "{$percentage}% increase from last month";
        }

        if ($growth < 0) {
            return $isArabic
                ? "انخفاض {$percentage}٪ عن الشهر الماضي"
                : "{$percentage}% decrease from last month";
        }

        return $isArabic
            ? 'لا يوجد تغيير عن الشهر الماضي'
            : 'No change from last month';
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

    protected function getDailyOrderTrend(): array
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
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
            ->selectRaw('DATE(created_at) as day, SUM(commission) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->values()
            ->toArray();
    }
}
