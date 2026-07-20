<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class TestPushController extends Controller
{
    public function send(Request $request, FirebaseNotificationService $fcmService)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $driver = Driver::findOrFail($validated['driver_id']);

        if (!$driver->fcm_token) {
            return response()->json([
                'status' => false,
                'message' => 'Driver does not have FCM token',
                'data' => null,
            ], 400);
        }

        $result = $fcmService->sendToToken(
            token: $driver->fcm_token,
            title: 'طلب جديد',
            body: 'لديك طلب توصيل جديد',
            data: [
                'type' => 'new_order',
                'order_id' => (string) $validated['order_id'],
                'driver_id' => (string) $driver->id,
            ],
            appType: 'driver',
        );

        return response()->json([
            'status' => true,
            'message' => 'Push notification sent successfully',
            'data' => $result,
        ]);
    }
}
