<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FirebaseTrackingService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function updateOrderLocation(
        Request $request,
        Order $order,
        FirebaseTrackingService $tracking
    ) {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'heading' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric', 'min:0'],
        ]);

        $driver = $request->user()->driver;

        if (!$driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 403);
        }

        if ((int) $order->driver_id !== (int) $driver->id) {
            return response()->json([
                'message' => 'You are not allowed to update this order location.',
            ], 403);
        }
        if ($order->status !== 'delivering') {
            return response()->json([
                'message' => 'Tracking is only allowed while order is delivering.',
            ], 422);
        }

        $tracking->updateOrderLocation(
            orderId: $order->id,
            driverId: $driver->id,
            lat: (float) $data['lat'],
            lng: (float) $data['lng'],
            heading: isset($data['heading']) ? (float) $data['heading'] : null,
            speed: isset($data['speed']) ? (float) $data['speed'] : null,
        );

        return response()->json([
            'message' => 'Order location updated successfully.',
        ]);
    }

    public function stopOrderTracking(
        Request $request,
        Order $order,
        FirebaseTrackingService $tracking
    ) {
        $driver = $request->user()->driver;

        if (!$driver) {
            return response()->json([
                'message' => 'Driver profile not found.',
            ], 403);
        }

        if ((int) $order->driver_id !== (int) $driver->id) {
            return response()->json([
                'message' => 'You are not allowed to stop this order tracking.',
            ], 403);
        }

        $tracking->stopOrderTracking($order->id);

        return response()->json([
            'message' => 'Order tracking stopped successfully.',
        ]);
    }
}
