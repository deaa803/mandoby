<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseTrackingService
{
    private $database;

    public function __construct()
    {
        $this->database = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();
    }

    public function updateOrderLocation(
        int $orderId,
        int $driverId,
        float $lat,
        float $lng,
        ?float $heading = null,
        ?float $speed = null
    ): void {
        $this->database
            ->getReference("tracking/orders/{$orderId}")
            ->set([
                'order_id' => $orderId,
                'driver_id' => $driverId,
                'lat' => $lat,
                'lng' => $lng,
                'heading' => $heading,
                'speed' => $speed,
                'updated_at' => now()->toIso8601String(),
                'updated_at_timestamp' => now()->timestamp,
            ]);
    }

    public function stopOrderTracking(int $orderId): void
    {
        $this->database
            ->getReference("tracking/orders/{$orderId}")
            ->remove();
    }
}
