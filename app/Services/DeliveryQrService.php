<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class DeliveryQrService
{
    private const PREFIX = 'ORD';
    private const RANDOM_LENGTH = 8;

    public function generate(Order $order): string
    {
        if (! empty($order->delivery_qr_code)) {
            return $order->delivery_qr_code;
        }

        $code = $this->generateUniqueCode($order);

        $order->forceFill([
            'delivery_qr_code' => $code,
        ])->save();

        return $code;
    }

    private function generateUniqueCode(Order $order): string
    {
        do {
            $code = $this->buildCode($order);
        } while ($this->codeExists($code));

        return $code;
    }

    private function buildCode(Order $order): string
    {
        return self::PREFIX . '-' . $order->id . '-' . strtoupper(Str::random(self::RANDOM_LENGTH));
    }

    private function codeExists(string $code): bool
    {
        return Order::where('delivery_qr_code', $code)->exists();
    }
}
