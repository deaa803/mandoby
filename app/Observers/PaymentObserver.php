<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PlatformProfitService;
use Illuminate\Validation\ValidationException;

class PaymentObserver
{
    /** @var array<int, int> */
    private static array $originalOrderIds = [];

    public function __construct(
        private readonly PlatformProfitService $platformProfitService,
    ) {
    }

    public function creating(Payment $payment): void
    {
        $this->validatePaymentAmount($payment);
    }

    public function updating(Payment $payment): void
    {
        self::$originalOrderIds[spl_object_id($payment)] = (int) $payment->getOriginal('order_id');

        $this->validatePaymentAmount($payment);
    }

    public function created(Payment $payment): void
    {
        $this->platformProfitService->syncOrderById((int) $payment->order_id);
    }

    public function updated(Payment $payment): void
    {
        $objectId = spl_object_id($payment);
        $previousOrderId = self::$originalOrderIds[$objectId] ?? null;

        unset(self::$originalOrderIds[$objectId]);

        if ($previousOrderId && $previousOrderId !== (int) $payment->order_id) {
            $this->platformProfitService->syncOrderById($previousOrderId);
        }

        $this->platformProfitService->syncOrderById((int) $payment->order_id);
    }

    public function deleted(Payment $payment): void
    {
        $this->platformProfitService->syncOrderById((int) $payment->order_id);
    }

    public function restored(Payment $payment): void
    {
        $this->platformProfitService->syncOrderById((int) $payment->order_id);
    }

    public function forceDeleted(Payment $payment): void
    {
        $this->platformProfitService->syncOrderById((int) $payment->order_id);
    }

    private function validatePaymentAmount(Payment $payment): void
    {
        $order = Order::query()->find($payment->order_id);

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => 'الطلب المحدد غير موجود.',
            ]);
        }

        $otherPayments = (float) Payment::query()
            ->where('order_id', $payment->order_id)
            ->when(
                $payment->exists,
                fn ($query) => $query->where('id', '!=', $payment->getKey()),
            )
            ->sum('amount');

        $newTotal = $otherPayments + (float) $payment->amount;

        if ($newTotal > (float) $order->total_price) {
            $remaining = max((float) $order->total_price - $otherPayments, 0);

            throw ValidationException::withMessages([
                'amount' => 'قيمة الدفعة أكبر من المبلغ المتبقي. الحد الأعلى المتاح هو '
                    . number_format($remaining, 2) . '.',
            ]);
        }
    }
}
