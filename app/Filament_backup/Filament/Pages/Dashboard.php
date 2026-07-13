<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CompanyProfitOverview;
use App\Filament\Widgets\DashboardControls;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    /**
     * عمود واحد على الهاتف وعمودان على الشاشات المتوسطة والكبيرة.
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }

    /**
     * تحديد Widgets اللوحة وترتيبها بشكل صريح.
     */
    public function getWidgets(): array
    {
        return [
            DashboardControls::class,
            StatsOverview::class,
            SalesChart::class,
            CompanyProfitOverview::class,
        ];
    }
}
