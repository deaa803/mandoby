<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DriverAppController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load('driver.company');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (!$user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Driver profile retrieved successfully',
            'data' => [
                'driver' => [
                    'id' => $user->driver->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'company_id' => $user->driver->company_id,
                    'company_name' => $user->driver->company?->name_company,
                    'vehicle_type' => $user->driver->vehicle_type,
                    'plate_number' => $user->driver->plate_number,
                    'is_active' => $user->driver->is_active,
                ],
            ],
        ]);
    }

    public function saveFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        $user = $request->user()->load('driver');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (!$user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        $user->driver->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'FCM token saved successfully',
            'data' => [
                'driver_id' => $user->driver->id,
            ],
        ]);
    }
    public function currentOrder(Request $request)
    {
        $user = $request->user()->load('driver');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (!$user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        $order = Order::with([
            'store.user',
            'productDetails.product',
            'productDetails.category',
            'productDetails.company',
            'productDetails.images',
        ])
            ->where('driver_id', $user->driver->id)
            ->whereIn('status', ['pending', 'preparing', 'delivering'])
            ->latest()
            ->first();

        if (!$order) {
            return response()->json([
                'status' => true,
                'message' => 'No current order found',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Current order found',
            'data' => [
                'order' => $order,
            ],
        ]);
    }

    public function deliveryHistory(Request $request)
    {
        $user = $request->user()->load('driver');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (!$user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        $orders = Order::with([
            'store.user',
            'productDetails.product',
            'productDetails.category',
            'productDetails.company',
            'productDetails.images',
        ])
            ->where('driver_id', $user->driver->id)
            ->where('status', 'delivered')
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No delivered orders found',
                'data' => [
                    'orders' => [],
                ],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Delivered orders found',
            'data' => [
                'orders' => $orders,
            ],
        ]);
    }
    public function markAsDelivered(Request $request, Order $order)
    {
        $user = $request->user()->load('driver');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (!$user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        if ((int) $order->driver_id !== (int) $user->driver->id) {
            return response()->json([
                'status' => false,
                'message' => 'This order is not assigned to this driver',
            ], 403);
        }

        if ($order->status === 'delivered') {
            return response()->json([
                'status' => true,
                'message' => 'Order already delivered',
                'data' => [
                    'order' => $order,
                ],
            ]);
        }

        $order->update([
            'status' => 'delivered',
        ]);

        $order->load([
            'store.user',
            'productDetails.product',
            'productDetails.category',
            'productDetails.company',
            'productDetails.images',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order marked as delivered successfully',
            'data' => [
                'order' => $order,
            ],
        ]);
    }
}
