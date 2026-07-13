<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product3DModel;
use App\Models\Store;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $companiesCount = Company::query()->count();
        $storesCount = Store::query()->count();

        $ordersQuery = Order::query()
            ->where('status', '!=', 'cancelled');

        $ordersCount = (clone $ordersQuery)->count();

        $activeOrdersCount = (clone $ordersQuery)
            ->whereIn('status', [
                'pending',
                'preparing',
                'delivering',
            ])
            ->count();

        $completedOrdersCount = (clone $ordersQuery)
            ->where('status', 'delivered')
            ->count();

        $totalRevenue = (float) (clone $ordersQuery)
            ->sum('total_price');

        $totalPayments = (float) Payment::query()
            ->sum('amount');

        $platformProfit = (float) (clone $ordersQuery)
            ->sum('commission');

        $completed3DModels = Product3DModel::query()
            ->where('status', 'completed')
            ->count();

        $companiesGrowth = $this->calculateMonthlyGrowth(
            Company::query()
        );

        $storesGrowth = $this->calculateMonthlyGrowth(
            Store::query()
        );

        $ordersGrowth = $this->calculateMonthlyGrowth(
            Order::query()->where('status', '!=', 'cancelled')
        );

        $revenueGrowth = $this->calculateMonthlySumGrowth(
            Order::query()->where('status', '!=', 'cancelled'),
            'total_price'
        );

        $profitGrowth = $this->calculateMonthlySumGrowth(
            Order::query()->where('status', '!=', 'cancelled'),
            'commission'
        );

        return [
            Stat::make(
                'الشركات المسجلة',
                number_format($companiesCount)
            )
                ->description(
                    $this->growthDescription($companiesGrowth)
                )
                ->descriptionIcon(
                    $companiesGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::BuildingOffice2)
                ->chart($this->getDailyCountTrend(Company::query()))
                ->color($companiesGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                'المتاجر المسجلة',
                number_format($storesCount)
            )
                ->description(
                    $this->growthDescription($storesGrowth)
                )
                ->descriptionIcon(
                    $storesGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::BuildingStorefront)
                ->chart($this->getDailyCountTrend(Store::query()))
                ->color($storesGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                'إجمالي الطلبات',
                number_format($ordersCount)
            )
                ->description(
                    $this->growthDescription($ordersGrowth)
                )
                ->descriptionIcon(
                    $ordersGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::ShoppingBag)
                ->chart($this->getOrderCountTrend())
                ->color($ordersGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                'الطلبات النشطة',
                number_format($activeOrdersCount)
            )
                ->description('قيد الانتظار أو التحضير أو التوصيل')
                ->icon(Heroicon::Truck)
                ->color('warning'),

            Stat::make(
                'الطلبات المكتملة',
                number_format($completedOrdersCount)
            )
                ->description('الطلبات التي تم تسليمها بنجاح')
                ->icon(Heroicon::CheckCircle)
                ->color('success'),

            Stat::make(
                'إجمالي المبيعات',
                number_format($totalRevenue, 2)
            )
                ->description(
                    $this->growthDescription($revenueGrowth)
                )
                ->descriptionIcon(
                    $revenueGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::Banknotes)
                ->chart($this->getDailySumTrend('total_price'))
                ->color($revenueGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                'أرباح المنصة',
                number_format($platformProfit, 2)
            )
                ->description(
                    $this->growthDescription($profitGrowth)
                )
                ->descriptionIcon(
                    $profitGrowth >= 0
                        ? Heroicon::ArrowTrendingUp
                        : Heroicon::ArrowTrendingDown
                )
                ->icon(Heroicon::CurrencyDollar)
                ->chart($this->getDailySumTrend('commission'))
                ->color($profitGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(
                'المدفوعات المسجلة',
                number_format($totalPayments, 2)
            )
                ->description('إجمالي الدفعات المستلمة')
                ->icon(Heroicon::CreditCard)
                ->chart($this->getPaymentTrend())
                ->color('info'),

            Stat::make(
                'نماذج المنتجات ثلاثية الأبعاد',
                number_format($completed3DModels)
            )
                ->description('النماذج الجاهزة للعرض')
                ->icon(Heroicon::CubeTransparent)
                ->color('primary'),
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

        return round(
            (($currentMonth - $previousMonth) / $previousMonth) * 100,
            1
        );
    }

    protected function calculateMonthlySumGrowth(
        Builder $query,
        string $column
    ): float {
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

        return round(
            (($currentMonth - $previousMonth) / $previousMonth) * 100,
            1
        );
    }

    protected function growthDescription(float $growth): string
    {
        $percentage = number_format(abs($growth), 1);

        if ($growth > 0) {
            return "زيادة {$percentage}٪ عن الشهر الماضي";
        }

        if ($growth < 0) {
            return "انخفاض {$percentage}٪ عن الشهر الماضي";
        }

        return 'لا يوجد تغيير عن الشهر الماضي';
    }

    protected function getDailyCountTrend(Builder $query): array
    {
        return (clone $query)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): int => (int) $value)
            ->values()
            ->toArray();
    }

    protected function getOrderCountTrend(): array
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): int => (int) $value)
            ->values()
            ->toArray();
    }

    protected function getDailySumTrend(string $column): array
    {
        return Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(created_at) as day, SUM({$column}) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->values()
            ->toArray();
    }

    protected function getPaymentTrend(): array
    {
        return Payment::query()
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->map(fn ($value): float => (float) $value)
            ->values()
            ->toArray();
    }
}
