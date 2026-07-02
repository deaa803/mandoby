<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        self::syncOrder($this->record->order_id);
    }

    private static function syncOrder(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            return;
        }

        $paidAmount = (float) $order->payments()->sum('amount');
        $totalPrice = (float) $order->total_price;

        $order->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => max(0, $totalPrice - $paidAmount),
        ]);
    }
}
