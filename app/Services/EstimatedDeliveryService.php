<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;

class EstimatedDeliveryService
{
    /**
     * متوسط سرعة داخل المدينة بالكيلومتر/ساعة.
     * يمكن لاحقاً نقلها إلى config/services.php أو .env.
     */
    private const CITY_AVERAGE_SPEED_KMH = 32;

    /**
     * هامش أمان بسيط بسبب الازدحام، التحميل، والتوقفات.
     */
    private const TRAFFIC_BUFFER_MINUTES = 10;

    public function estimate(Order $order): array
    {
        $order->loadMissing([
            'store.user',
            'driver.user',
            'productDetails.company.user',
        ]);

        if ($order->status === 'delivered') {
            return $this->result(0, now(), 'delivered_order');
        }

        if ($order->status === 'cancelled') {
            return $this->result(null, null, 'cancelled_order');
        }

        $prepMinutes = $this->preparationMinutes($order);
        $distanceKm = null;
        $source = 'status_fallback';

        $storeLat = $this->toFloat($order->store?->user?->latitude);
        $storeLng = $this->toFloat($order->store?->user?->longitude);

        if ($order->status === 'delivering' && $order->driver) {
            $driverLat = $this->toFloat($order->driver->current_lat);
            $driverLng = $this->toFloat($order->driver->current_lng);

            if ($this->hasCoordinates($driverLat, $driverLng, $storeLat, $storeLng)) {
                $distanceKm = $this->distanceKm($driverLat, $driverLng, $storeLat, $storeLng);
                $source = 'driver_to_store';
            }
        }

        if ($distanceKm === null) {
            $companyUser = $order->productDetails->first()?->company?->user;
            $companyLat = $this->toFloat($companyUser?->latitude);
            $companyLng = $this->toFloat($companyUser?->longitude);

            if ($this->hasCoordinates($companyLat, $companyLng, $storeLat, $storeLng)) {
                $distanceKm = $this->distanceKm($companyLat, $companyLng, $storeLat, $storeLng);
                $source = 'company_to_store';
            }
        }

        if ($distanceKm !== null) {
            $travelMinutes = (int) ceil(($distanceKm / self::CITY_AVERAGE_SPEED_KMH) * 60);
            $minutes = max(10, $prepMinutes + $travelMinutes + self::TRAFFIC_BUFFER_MINUTES);
        } else {
            $minutes = $this->fallbackMinutes($order->status);
        }

        return $this->result(
            minutes: $minutes,
            estimatedAt: now()->addMinutes($minutes),
            source: $source,
            distanceKm: $distanceKm,
        );
    }

    public function updateOrderEta(Order $order): Order
    {
        $eta = $this->estimate($order);

        $order->forceFill([
            'estimated_delivery_minutes' => $eta['minutes'],
            'estimated_delivery_at' => $eta['estimated_at'],
            'eta_last_calculated_at' => now(),
        ])->save();

        return $order->fresh();
    }

    private function preparationMinutes(Order $order): int
    {
        return match ($order->status) {
            'pending' => 120,
            'preparing' => 60,
            'delivering' => 0,
            default => 90,
        };
    }

    private function fallbackMinutes(string $status): ?int
    {
        return match ($status) {
            'pending' => 180,
            'preparing' => 90,
            'delivering' => 45,
            'delivered' => 0,
            'cancelled' => null,
            default => 120,
        };
    }

    private function result(?int $minutes, ?CarbonInterface $estimatedAt, string $source, ?float $distanceKm = null): array
    {
        return [
            'minutes' => $minutes,
            'estimated_at' => $estimatedAt,
            'estimated_at_human' => $estimatedAt?->diffForHumans(),
            'distance_km' => $distanceKm !== null ? round($distanceKm, 2) : null,
            'source' => $source,
        ];
    }

    private function hasCoordinates(?float ...$values): bool
    {
        foreach ($values as $value) {
            if ($value === null) {
                return false;
            }
        }

        return true;
    }

    private function toFloat(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Haversine distance in kilometers.
     */
    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
