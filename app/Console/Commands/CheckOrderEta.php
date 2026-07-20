<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\AppNotificationService;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class CheckOrderEta extends Command
{
    protected $signature = 'orders:check-eta';

    protected $description = 'Check active order ETA and send warning or late notifications.';

    public function handle(
        AppNotificationService $notifications,
        FirebaseNotificationService $firebase,
    ): int {
        Order::query()
            ->with([
                'driver.user',
                'store.user',
                'productDetails.company.user',
            ])
            ->whereIn('status', ['preparing', 'delivering'])
            ->whereNotNull('estimated_delivery_at')
            ->chunkById(100, function ($orders) use ($notifications, $firebase) {
                foreach ($orders as $order) {
                    $remainingMinutes = now()->diffInMinutes(
                        $order->estimated_delivery_at,
                        false,
                    );

                    if (
                        $remainingMinutes > 0 &&
                        $remainingMinutes <= 15 &&
                        !$order->eta_warning_sent_at
                    ) {
                        $this->notifyDriver(
                            $order,
                            $notifications,
                            $firebase,
                            'موعد الوصول يقترب',
                            'يرجى مراجعة الشحنة والعمل على تسليمها ضمن الوقت المتوقع',
                            'order_eta_warning',
                        );

                        $order->forceFill([
                            'eta_warning_sent_at' => now(),
                        ])->save();
                    }

                    if ($remainingMinutes <= 0 && !$order->eta_late_sent_at) {
                        $this->notifyLateOrder($order, $notifications, $firebase);

                        $order->forceFill([
                            'eta_late_sent_at' => now(),
                        ])->save();
                    }
                }
            });

        $this->info('Order ETA checked successfully.');

        return self::SUCCESS;
    }

    private function notifyDriver(
        Order $order,
        AppNotificationService $notifications,
        FirebaseNotificationService $firebase,
        string $title,
        string $body,
        string $type,
    ): void {
        $driver = $order->driver;

        if (!$driver?->user_id) {
            return;
        }

        $data = [
            'type' => $type,
            'order_id' => (string) $order->id,
        ];

        $notification = $notifications->create(
            userId: $driver->user_id,
            title: $title,
            body: $body,
            type: $type,
            orderId: $order->id,
            data: $data,
        );

        if ($driver->fcm_token) {
            $firebase->sendToToken(
                token: $driver->fcm_token,
                title: $notification->title,
                body: $notification->body,
                data: $notification->data ?? [],
                appType: 'driver',
            );

            return;
        }

        $firebase->sendToUser(
            userId: $driver->user_id,
            title: $notification->title,
            body: $notification->body,
            data: $notification->data ?? [],
            appType: 'driver',
        );
    }

    private function notifyLateOrder(
        Order $order,
        AppNotificationService $notifications,
        FirebaseNotificationService $firebase,
    ): void {
        $this->notifyDriver(
            $order,
            $notifications,
            $firebase,
            'تأخر موعد الوصول',
            'تجاوزت الشحنة وقت الوصول المتوقع، يرجى مراجعتها الآن',
            'order_eta_late',
        );

        $recipients = collect([$order->store?->user])
            ->merge(
                $order->productDetails
                    ->pluck('company.user')
                    ->filter()
            )
            ->filter()
            ->unique('id');

        foreach ($recipients as $user) {
            $appType = $user->user_type === 'store' ? 'store' : 'company';
            $data = [
                'type' => 'order_eta_late',
                'order_id' => (string) $order->id,
            ];

            $notification = $notifications->create(
                userId: $user->id,
                title: 'تأخر موعد الوصول',
                body: 'تجاوزت الشحنة وقت الوصول المتوقع، يمكنك مراجعة التفاصيل داخل التطبيق',
                type: 'order_eta_late',
                orderId: $order->id,
                data: $data,
            );

            $firebase->sendToUser(
                userId: $user->id,
                title: $notification->title,
                body: $notification->body,
                data: $notification->data ?? [],
                appType: $appType,
            );
        }
    }
}
