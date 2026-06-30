<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FcmService
{
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): array {
        $projectId = env('FIREBASE_PROJECT_ID');
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsPath
        );

        $accessToken = $credentials->fetchAuthToken()['access_token'];

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,

                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],

                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'channel_id' => 'driver_orders_channel',
                            'sound' => 'default',
                        ],
                    ],

                    'data' => array_map('strval', $data),
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}
