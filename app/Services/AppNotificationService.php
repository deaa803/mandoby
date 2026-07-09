<?php

namespace App\Services;

use App\Models\AppNotification;

class AppNotificationService
{
    public function create(
        int $userId,
        string $title,
        string $body,
        string $type,
        ?int $orderId = null,
        array $data = []
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $this->normalizeData($data),
        ]);
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[(string) $key] = is_scalar($value) || $value === null
                ? $value
                : json_encode($value);
        }

        return $normalized;
    }
}
