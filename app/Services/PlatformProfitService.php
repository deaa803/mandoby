<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class PlatformProfitService
{
    /**
     * نسبة عمولة المنصة من الدفعات الفعلية.
     * 0.02 تعني 2٪.
     */
    public const COMMISSION_RATE = 0.02;

    public function rate(): float
    {
        return self::COMMISSION_RATE;
    }

    public function calculateFromPaidAmount(float|int|string|null $paidAmount): float
    {
        return round(((float) $paidAmount) * $this->rate(), 2);
    }

    public function syncOrder(Order $order): Order
    {
        $paidAmount = (float) Payment::query()
            ->where('order_id', $order->getKey())
            ->sum('amount');

        $totalPrice = (float) $order->total_price;

        $order->forceFill([
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => round(max($totalPrice - $paidAmount, 0), 2),
            'commission' => $this->calculateFromPaidAmount($paidAmount),
        ])->saveQuietly();

        return $order->refresh();
    }

    public function syncOrderById(int $orderId): ?Order
    {
        $order = Order::query()->find($orderId);

        return $order ? $this->syncOrder($order) : null;
    }

    public function syncAllOrders(int $chunkSize = 200): int
    {
        $updated = 0;

        Order::query()
            ->orderBy('id')
            ->chunkById($chunkSize, function (Collection $orders) use (&$updated): void {
                foreach ($orders as $order) {
                    $this->syncOrder($order);
                    $updated++;
                }
            });

        return $updated;
    }
}
