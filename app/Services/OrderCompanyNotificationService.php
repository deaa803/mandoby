<?php

namespace App\Services;

use App\Models\Order;
use Throwable;

class OrderCompanyNotificationService
{
    public function __construct(
        private FirebaseNotificationService $firebase,
        private AppNotificationService $notifications
    ) {
    }

    public function notifyNewOrder(Order $order): array
    {
        $order->loadMissing([
            'store.user',
            'productDetails.company',
        ]);

        $companies = $order->productDetails
            ->pluck('company')
            ->filter()
            ->unique('id')
            ->values();

        $results = [];

        foreach ($companies as $company) {
            if (! $company->user_id) {
                $results[] = [
                    'company_id' => $company->id,
                    'success' => false,
                    'message' => 'Company does not have user_id.',
                ];

                continue;
            }

            try {
                $title = 'طلب جديد';
                $body = "وصلك طلب جديد، يرجى مراجعة التفاصيل داخل التطبيق";
                $data = [
                    'type' => 'new_order',
                    'order_id' => (string) $order->id,
                    'store_id' => (string) $order->store_id,
                ];

                $notification = $this->notifications->create(
                    userId: $company->user_id,
                    title: $title,
                    body: $body,
                    type: 'new_order',
                    orderId: $order->id,
                    data: $data
                );

                $results[] = [
                    'company_id' => $company->id,
                    'notification_id' => $notification->id,
                    'result' => $this->firebase->sendToUser(
                        userId: $company->user_id,
                        title: $title,
                        body: $body,
                        data: $data,
                        appType: 'company'
                    ),
                ];
            } catch (Throwable $e) {
                report($e);

                $results[] = [
                    'company_id' => $company->id,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
