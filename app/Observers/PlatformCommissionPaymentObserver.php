<?php

namespace App\Observers;

use App\Models\PlatformCommissionPayment;
use App\Services\PlatformProfitService;
use Illuminate\Validation\ValidationException;

class PlatformCommissionPaymentObserver
{
    private ?int $originalCompanyId = null;

    public function __construct(
        private readonly PlatformProfitService $platformProfitService,
    ) {
    }

    public function creating(PlatformCommissionPayment $payment): void
    {
        $this->validateAmount($payment);
        $this->normalizeReviewData($payment);
    }

    public function updating(PlatformCommissionPayment $payment): void
    {
        $this->originalCompanyId = (int) $payment->getOriginal('company_id');
        $this->validateAmount($payment);
        $this->normalizeReviewData($payment);
    }

    public function created(PlatformCommissionPayment $payment): void
    {
        $this->platformProfitService->forgetCompany((int) $payment->company_id);
    }

    public function updated(PlatformCommissionPayment $payment): void
    {
        if ($this->originalCompanyId) {
            $this->platformProfitService->forgetCompany($this->originalCompanyId);
        }

        $this->platformProfitService->forgetCompany((int) $payment->company_id);
    }

    public function deleted(PlatformCommissionPayment $payment): void
    {
        $this->platformProfitService->forgetCompany((int) $payment->company_id);
    }

    private function validateAmount(PlatformCommissionPayment $payment): void
    {
        $amount = (float) $payment->amount;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'يجب أن يكون مبلغ الدفعة أكبر من صفر.',
            ]);
        }

        $companyId = (int) $payment->company_id;
        $excludeId = $payment->exists ? (int) $payment->getKey() : null;
        $accrued = $this->platformProfitService->accruedForCompany($companyId);
        $confirmed = $this->platformProfitService
            ->confirmedPaidForCompany($companyId, $excludeId);
        $pending = $this->platformProfitService
            ->pendingPaidForCompany($companyId, $excludeId);

        $reservedBeforeNewPayment = $payment->status === 'pending'
            ? $confirmed + $pending
            : $confirmed;

        $maximumAvailable = round(max($accrued - $reservedBeforeNewPayment, 0), 2);

        if ($amount > $maximumAvailable + 0.009) {
            throw ValidationException::withMessages([
                'amount' => 'قيمة الدفعة أكبر من المستحق المتاح. الحد الأعلى هو '
                    . number_format($maximumAvailable, 2) . '.',
            ]);
        }
    }

    private function normalizeReviewData(PlatformCommissionPayment $payment): void
    {
        if ($payment->status === 'pending') {
            $payment->reviewed_by_user_id = null;
            $payment->reviewed_at = null;
            $payment->rejection_reason = null;

            return;
        }

        $payment->reviewed_by_user_id ??= auth()->id();
        $payment->reviewed_at ??= now();

        if ($payment->status === 'confirmed') {
            $payment->rejection_reason = null;
        }
    }
}
