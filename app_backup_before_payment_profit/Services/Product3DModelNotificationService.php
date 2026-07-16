<?php

namespace App\Services;

use App\Models\Product3DModel;

class Product3DModelNotificationService
{
    public function __construct(
        private AppNotificationService $notifications,
        private FirebaseNotificationService $firebase,
    ) {
    }

    public function notifyCompleted(Product3DModel $model): void
    {
        $model->loadMissing('company.user');

        if (
            $model->status !== 'completed' ||
            $model->completed_notification_sent_at ||
            !$model->company?->user
        ) {
            return;
        }

        $user = $model->company->user;
        $data = [
            'type' => '3d_model_completed',
            'model_id' => (string) $model->id,
            'product_detail_id' => (string) $model->product_detail_id,
        ];

        $notification = $this->notifications->create(
            userId: $user->id,
            title: 'المودل ثلاثي الأبعاد جاهز',
            body: 'تم تجهيز المودل ثلاثي الأبعاد ويمكنك مشاهدته داخل التطبيق',
            type: '3d_model_completed',
            data: $data,
        );

        $this->firebase->sendToUser(
            userId: $user->id,
            title: $notification->title,
            body: $notification->body,
            data: $notification->data ?? [],
            appType: 'company',
        );

        $model->forceFill([
            'completed_notification_sent_at' => now(),
        ])->save();
    }
}
