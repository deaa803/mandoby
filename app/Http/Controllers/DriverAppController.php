<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use App\Services\FirebaseNotificationService;
use App\Services\AppNotificationService;
use App\Services\FirebaseTrackingService;
use App\Services\DeliveryConfirmationService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverAppController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load('driver.car.company');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (! $user->driver || ! $user->driver->car) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile or assigned car not found',
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
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'company_id' => $user->driver->car->company_id,
                    'company_name' => $user->driver->car->company?->name_company,
                    'company_car_id' => $user->driver->company_car_id,
                    'vehicle_type' => $user->driver->car->vehicle_type,
                    'plate_number' => $user->driver->car->plate_number,
                    'status' => $user->driver->status,
                ],
            ],
        ]);
    }

    public function saveFcmToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $user = $request->user()->load('driver');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (! $user->driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver profile not found',
            ], 404);
        }

        DB::transaction(function () use ($validated, $user) {
            Driver::where('fcm_token', $validated['fcm_token'])
                ->where('id', '!=', $user->driver->id)
                ->update(['fcm_token' => null]);

            $user->driver->update([
                'fcm_token' => $validated['fcm_token'],
            ]);
        });

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

        if (! $user->driver) {
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

        if (! $order) {
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

        if (! $user->driver) {
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

        return response()->json([
            'status' => true,
            'message' => $orders->isEmpty()
                ? 'No delivered orders found'
                : 'Delivered orders found',
            'data' => [
                'orders' => $orders,
            ],
        ]);
    }

    public function markAsDelivered(
        Request $request,
        Order $order,
        FirebaseNotificationService $firebase,
        FirebaseTrackingService $tracking,
        AppNotificationService $notifications,
        DeliveryConfirmationService $confirmationService
    ) {
        $user = $request->user()->load('driver');

        if ($user->user_type !== 'driver') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a driver account',
            ], 403);
        }

        if (! $user->driver) {
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
            $order->load([
                'store.user',
                'productDetails.product',
                'productDetails.category',
                'productDetails.company',
                'productDetails.images',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Order already delivered',
                'data' => [
                    'order' => $order,
                ],
            ]);
        }

        $validated = $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        try {
            $order = $confirmationService->confirm(
                order: $order,
                driver: $user->driver,
                qrCode: $validated['qr_code'],
            );
        } catch (ValidationException $e) {
            throw $e;
        }

        $tracking->stopOrderTracking($order->id);

        $order->load([
            'store.user',
            'productDetails.product',
            'productDetails.category',
            'productDetails.company',
            'productDetails.images',
        ]);

        $companies = $order->productDetails
            ->pluck('company')
            ->filter()
            ->unique('id')
            ->values();

        $companyPushResults = [];

        foreach ($companies as $company) {
            if (! $company->user_id) {
                $companyPushResults[] = [
                    'company_id' => $company->id,
                    'success' => false,
                    'message' => 'Company does not have user_id.',
                ];

                continue;
            }

            $title = 'تم تسليم الشحنة';
            $body = "قام السائق {$user->name} بتسليم الشحنة بنجاح";
            $data = [
                'type' => 'order_delivered',
                'order_id' => (string) $order->id,
                'driver_id' => (string) $user->driver->id,
            ];

            $notification = $notifications->create(
                userId: $company->user_id,
                title: $title,
                body: $body,
                type: 'order_delivered',
                orderId: $order->id,
                data: $data
            );

            $companyPushResults[] = [
                'company_id' => $company->id,
                'notification_id' => $notification->id,
                'result' => $firebase->sendToUser(
                    userId: $company->user_id,
                    title: $title,
                    body: $body,
                    data: $data,
                    appType: 'company'
                ),
            ];
        }

        $storePushResult = [
            'success' => false,
            'message' => 'Store does not have a user account.',
        ];

        $storeNotification = null;

        if ($order->store?->user_id) {
            $storeNotification = $notifications->create(
                userId: $order->store->user_id,
                title: 'تم تسليم الشحنة',
                body: 'تم تسليم شحنتك بنجاح',
                type: 'store_order_delivered',
                orderId: $order->id,
                data: [
                    'type' => 'store_order_delivered',
                    'order_id' => (string) $order->id,
                    'driver_id' => (string) $user->driver->id,
                ]
            );

            $storePushResult = $firebase->sendToUser(
                userId: $order->store->user_id,
                title: $storeNotification->title,
                body: $storeNotification->body,
                data: $storeNotification->data ?? [],
                appType: 'store'
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Order marked as delivered successfully',
            'data' => [
                'order' => $order,
                'company_push_results' => $companyPushResults,
                'store_notification_id' => $storeNotification?->id,
                'store_push_result' => $storePushResult,
            ],
        ]);
    }
}
