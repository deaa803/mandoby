<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\Widget;

class RecentOrders extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.recent-orders';

    protected function getViewData(): array
    {
        $orders = Order::query()
            ->with(['store.user', 'driver.user'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (Order $order) {
                $order->admin_url = OrderResource::getUrl('view', ['record' => $order]);
                return $order;
            });

        return [
            'orders' => $orders,
            'allOrdersUrl' => OrderResource::getUrl('index'),
            'currency' => config('app.currency', 'SYP'),
            'isArabic' => true,
        ];
    }
}
