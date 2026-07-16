<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CompanyProfitOverview;
use App\Filament\Widgets\DashboardHero;
use App\Filament\Widgets\PlatformInsights;
use App\Filament\Widgets\RecentOrders;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected ?string $heading = 'لوحة التحكم';

    protected ?string $subheading = 'مركز متابعة أعمال المنصة واتخاذ القرار';

    public function getColumns(): array|int
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
            DashboardHero::class,
            StatsOverview::class,
            SalesChart::class,
            CompanyProfitOverview::class,
            RecentOrders::class,
            PlatformInsights::class,
        ];
    }
}
