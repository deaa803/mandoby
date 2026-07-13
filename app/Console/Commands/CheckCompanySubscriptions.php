<?php

namespace App\Console\Commands;

use App\Models\CompanySubscription;
use App\Services\AppNotificationService;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class CheckCompanySubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiry';

    protected $description = 'Check company subscriptions and send expiry notifications.';

    public function handle(
        AppNotificationService $notifications,
        FirebaseNotificationService $firebase
    ): int {
        $subscriptions = CompanySubscription::query()
            ->with(['company.user', 'plan'])
            ->whereIn('status', [
                CompanySubscription::STATUS_ACTIVE,
                CompanySubscription::STATUS_EXPIRING,
            ])
            ->whereNotNull('end_date')
            ->get();

        foreach ($subscriptions as $subscription) {
            $daysRemaining = $subscription->days_remaining;

            if ($daysRemaining === null) {
                continue;
            }

            if ($daysRemaining < 0) {
                $this->markExpired($subscription, $notifications, $firebase);
                continue;
            }

            if ($daysRemaining <= 7 && $subscription->status !== CompanySubscription::STATUS_EXPIRING) {
                $subscription->update(['status' => CompanySubscription::STATUS_EXPIRING]);
            }

            if ($daysRemaining <= 7 && !$subscription->last_expiring_7_notified_at) {
                $this->notify(
                    subscription: $subscription,
                    notifications: $notifications,
                    firebase: $firebase,
                    title: 'قرب انتهاء الاشتراك',
                    body: "اشتراك {$subscription->plan?->name} أوشك على الانتهاء، يرجى مراجعته داخل التطبيق.",
                    type: 'subscription_expiring_7_days',
                );

                $subscription->update(['last_expiring_7_notified_at' => now()]);
            }

            if ($daysRemaining <= 3 && !$subscription->last_expiring_3_notified_at) {
                $this->notify(
                    subscription: $subscription,
                    notifications: $notifications,
                    firebase: $firebase,
                    title: 'تنبيه اشتراك',
                    body: "اشتراك {$subscription->plan?->name} قريب جدًا من الانتهاء، يرجى تجديده.",
                    type: 'subscription_expiring_3_days',
                );

                $subscription->update(['last_expiring_3_notified_at' => now()]);
            }
        }

        $this->info('Company subscriptions checked successfully.');

        return self::SUCCESS;
    }

    private function markExpired(
        CompanySubscription $subscription,
        AppNotificationService $notifications,
        FirebaseNotificationService $firebase
    ): void {
        if ($subscription->status !== CompanySubscription::STATUS_EXPIRED) {
            $subscription->update(['status' => CompanySubscription::STATUS_EXPIRED]);
        }

        if ($subscription->expired_notified_at) {
            return;
        }

        $this->notify(
            subscription: $subscription,
            notifications: $notifications,
            firebase: $firebase,
            title: 'انتهى الاشتراك',
            body: "انتهى اشتراك {$subscription->plan?->name}. تم إيقاف الميزات المدفوعة حتى التجديد.",
            type: 'subscription_expired',
        );

        $subscription->update(['expired_notified_at' => now()]);
    }

    private function notify(
        CompanySubscription $subscription,
        AppNotificationService $notifications,
        FirebaseNotificationService $firebase,
        string $title,
        string $body,
        string $type,
    ): void {
        $user = $subscription->company?->user;

        if (!$user) {
            return;
        }

        $notification = $notifications->create(
            userId: $user->id,
            title: $title,
            body: $body,
            type: $type,
            data: [
                'type' => $type,
                'company_subscription_id' => (string) $subscription->id,
                'subscription_plan_id' => (string) $subscription->subscription_plan_id,
                'end_date' => optional($subscription->end_date)->toDateTimeString(),
            ]
        );

        $firebase->sendToUser(
            userId: $user->id,
            title: $notification->title,
            body: $notification->body,
            data: $notification->data ?? [],
            appType: 'company',
        );
    }
}
