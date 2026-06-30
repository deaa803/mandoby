<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\FcmService;
use Illuminate\Http\Request;

class TestPushController extends Controller
{
    public function send(Request $request, FcmService $fcmService)
    {
        $driverId = $request->input('driver_id', 1);

        $driver = Driver::findOrFail($driverId);

        if (!$driver->fcm_token) {
            return response()->json([
                'status' => false,
                'message' => 'Driver does not have FCM token',
            ], 400);
        }

        $result = $fcmService->sendToToken(
            token: $driver->fcm_token,
            title: 'طلب جديد',
            body: 'لديك طلب توصيل جديد',
            data: [
                'type' => 'new_order',
                'order_id' => 101,
                'driver_id' => $driver->id,
            ],
        );

        return response()->json([
            'status' => true,
            'message' => 'Push notification sent successfully',
            'data' => $result,
        ]);
    }
}
