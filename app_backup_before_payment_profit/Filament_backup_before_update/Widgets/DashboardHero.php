<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Stores\StoreResource;
use App\Models\Order;
use Filament\Widgets\Widget;

class DashboardHero extends Widget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-hero';

    protected function getViewData(): array
    {
        $todayOrders = Order::query()
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        $pendingOrders = Order::query()
            ->whereIn('status', ['pending', 'preparing', 'delivering'])
            ->count();

        $todaySales = (float) Order::query()
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');

        $todayProfit = (float) Order::query()
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('commission');

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'صباح الخير' : ($hour < 18 ? 'مساء الخير' : 'مساء النور');

        return [
            'greeting' => $greeting,
            'dateLabel' => now()->translatedFormat('l، d F Y'),
            'todayOrders' => $todayOrders,
            'pendingOrders' => $pendingOrders,
            'todaySales' => $todaySales,
            'todayProfit' => $todayProfit,
            'currency' => config('app.currency', 'SYP'),
            'isArabic' => app()->getLocale() !== 'en',
            'links' => [
                'company' => CompanyResource::getUrl('create'),
                'store' => StoreResource::getUrl('create'),
                'product' => ProductResource::getUrl('create'),
                'order' => OrderResource::getUrl('create'),
            ],
        ];
    }
}
