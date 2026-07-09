<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryConfirmationService
{
    public function confirm(Order $order, Driver $driver, string $qrCode): Order
    {
        $this->validateOrderCanBeDelivered($order, $driver, $qrCode);

        return DB::transaction(function () use ($order, $driver) {
            $order->forceFill([
                'status' => 'delivered',
                'delivery_qr_used_at' => now(),
                'delivered_at' => now(),
            ])->save();

            $driver->forceFill([
                'status' => 'available',
            ])->save();

            return $order->fresh();
        });
    }

    private function validateOrderCanBeDelivered(Order $order, Driver $driver, string $qrCode): void
    {
        if ((int) $order->driver_id !== (int) $driver->id) {
            throw ValidationException::withMessages([
                'order' => 'This order is not assigned to this driver.',
            ]);
        }

        if ($order->status !== 'delivering') {
            throw ValidationException::withMessages([
                'order' => 'Only delivering orders can be confirmed as delivered.',
            ]);
        }

        if (! empty($order->delivery_qr_used_at)) {
            throw ValidationException::withMessages([
                'qr_code' => 'This QR code has already been used.',
            ]);
        }

        if (! hash_equals((string) $order->delivery_qr_code, trim($qrCode))) {
            throw ValidationException::withMessages([
                'qr_code' => 'Invalid delivery QR code.',
            ]);
        }
    }
}
