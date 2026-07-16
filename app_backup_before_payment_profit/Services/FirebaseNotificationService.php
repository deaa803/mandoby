<?php

namespace App\Services;

use App\Models\DeviceToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class FirebaseNotificationService
{
    private $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->createMessaging();
    }

    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): array {
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($this->normalizeData($data));

            $this->messaging->send($message);

            return [
                'success' => true,
                'sent' => 1,
                'failed' => 0,
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'sent' => 0,
                'failed' => 1,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        array $data = [],
        ?string $appType = null
    ): array {
        $query = DeviceToken::query()
            ->where('user_id', $userId);

        if ($appType) {
            $query->where('app_type', $appType);
        }

        $tokens = $query->pluck('fcm_token')->filter()->values();

        if ($tokens->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No device tokens found.',
            ];
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($tokens as $token) {
            $result = $this->sendToToken(
                token: $token,
                title: $title,
                body: $body,
                data: $data,
            );

            $successCount += $result['sent'];
            $failedCount += $result['failed'];
        }

        return [
            'success' => $successCount > 0,
            'sent' => $successCount,
            'failed' => $failedCount,
        ];
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[(string) $key] = (string) $value;
        }

        return $normalized;
    }
}
