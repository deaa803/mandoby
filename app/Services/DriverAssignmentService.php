<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DriverAssignmentService
{
    private array $relations = [
        'store.user',
        'driver.user',
        'driver.car.company',
        'productDetails.product',
        'productDetails.company',
        'productDetails.category',
        'productDetails.images',
        'productDetails.discount',
        'payments',
    ];

    public function __construct(
        private readonly SmartDispatchService $smartDispatch,
        private readonly FirebaseNotificationService $fcmService,
        private readonly AppNotificationService $notifications,
        private readonly EstimatedDeliveryService $etaService,
    ) {
    }

    public function assign(Order $order, Driver $driver, string $method = 'manual'): array
    {
        $method = $method === 'smart' ? 'smart' : 'manual';
        $validation = $this->smartDispatch->validateDriverForOrder($order, $driver);

        DB::transaction(function () use ($order, $driver, $method): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->driver_id && (int) $lockedOrder->driver_id !== (int) $driver->id) {
                Driver::whereKey($lockedOrder->driver_id)->update(['status' => 'available']);
            }

            $lockedOrder->update([
                'driver_id' => $driver->id,
                'driver_assignment_method' => $method,
                'driver_assigned_at' => now(),
                'status' => 'delivering',
            ]);

            $driver->update(['status' => 'busy']);
        });

        $order = $this->etaService->updateOrderEta($order->fresh());
        $order->load($this->relations);
        $driver->loadMissing(['user', 'car.company']);

        $driverPushResult = [
            'success' => false,
            'message' => 'Driver does not have an FCM token.',
        ];

        $driverNotification = $this->notifications->create(
            userId: $driver->user_id,
            title: 'شحنة توصيل جديدة',
            body: 'تم إسناد شحنة جديدة إليك، يرجى مراجعة التفاصيل داخل التطبيق',
            type: 'driver_assigned_order',
            orderId: $order->id,
            data: [
                'type' => 'driver_assigned_order',
                'order_id' => (string) $order->id,
                'driver_id' => (string) $driver->id,
                'assignment_method' => $method,
            ],
        );

        if ($driver->fcm_token) {
            $driverPushResult = $this->fcmService->sendToToken(
                token: $driver->fcm_token,
                title: $driverNotification->title,
                body: $driverNotification->body,
                data: $driverNotification->data ?? [],
            );
        }

        $storePushResult = [
            'success' => false,
            'message' => 'Store does not have a user account.',
        ];
        $storeNotification = null;

        if ($order->store?->user_id) {
            $storeNotification = $this->notifications->create(
                userId: $order->store->user_id,
                title: 'تم إسناد سائق للشحنة',
                body: "تم إسناد السائق {$driver->user?->name} للشحنة، يمكنك متابعة التفاصيل داخل التطبيق",
                type: 'driver_assigned_to_order',
                orderId: $order->id,
                data: [
                    'type' => 'driver_assigned_to_order',
                    'order_id' => (string) $order->id,
                    'driver_id' => (string) $driver->id,
                    'assignment_method' => $method,
                ],
            );

            $storePushResult = $this->fcmService->sendToUser(
                userId: $order->store->user_id,
                title: $storeNotification->title,
                body: $storeNotification->body,
                data: $storeNotification->data ?? [],
                appType: 'store',
            );
        }

        return [
            'order' => $order,
            'assignment_method' => $method,
            'dispatch_validation' => $validation,
            'driver_notification_id' => $driverNotification->id ?? null,
            'driver_push_result' => $driverPushResult,
            'store_notification_id' => $storeNotification?->id,
            'store_push_result' => $storePushResult,
        ];
    }
}
