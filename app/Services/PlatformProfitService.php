<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformCommissionPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PlatformProfitService
{
    /**
     * نسبة عمولة المنصة من الدفعات الفعلية للطلبات.
     * 0.02 تعني 2٪.
     */
    public const COMMISSION_RATE = 0.02;

    /** @var array<int, array<string, float|int|string>> */
    private array $companyStatementCache = [];

    public function rate(): float
    {
        return self::COMMISSION_RATE;
    }

    public function calculateFromPaidAmount(float|int|string|null $paidAmount): float
    {
        return round(((float) $paidAmount) * $this->rate(), 2);
    }

    /**
     * مزامنة قيم الطلب بعد إضافة أو تعديل أو حذف دفعة متجر.
     */
    public function syncOrder(Order $order): Order
    {
        $paidAmount = (float) Payment::query()
            ->where('order_id', $order->getKey())
            ->sum('amount');

        $totalPrice = (float) $order->total_price;

        $order->forceFill([
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => round(max($totalPrice - $paidAmount, 0), 2),
            'commission' => $this->calculateFromPaidAmount($paidAmount),
        ])->saveQuietly();

        $companyIds = $order->productDetails()
            ->pluck('product_details.company_id')
            ->unique()
            ->map(fn ($id): int => (int) $id);

        foreach ($companyIds as $companyId) {
            $this->forgetCompany($companyId);
        }

        return $order->refresh();
    }

    public function syncOrderById(int $orderId): ?Order
    {
        $order = Order::query()->find($orderId);

        return $order ? $this->syncOrder($order) : null;
    }

    public function syncAllOrders(int $chunkSize = 200): int
    {
        $updated = 0;

        Order::query()
            ->orderBy('id')
            ->chunkById($chunkSize, function (Collection $orders) use (&$updated): void {
                foreach ($orders as $order) {
                    $this->syncOrder($order);
                    $updated++;
                }
            });

        $this->companyStatementCache = [];

        return $updated;
    }

    /**
     * مجموع دفعات المتاجر على طلبات شركة محددة.
     */
    public function paidOrdersTotalForCompany(Company|int $company): float
    {
        $companyId = $company instanceof Company ? (int) $company->getKey() : $company;

        return round((float) $this->validOrderPaymentsForCompany($companyId)->sum('amount'), 2);
    }

    /**
     * عمولة المنصة المستحقة على الشركة من دفعات الطلبات الفعلية.
     */
    public function accruedForCompany(Company|int $company): float
    {
        return $this->calculateFromPaidAmount($this->paidOrdersTotalForCompany($company));
    }

    public function confirmedPaidForCompany(
        Company|int $company,
        ?int $excludePaymentId = null,
    ): float {
        $companyId = $company instanceof Company ? (int) $company->getKey() : $company;

        return round((float) PlatformCommissionPayment::query()
            ->where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->when(
                $excludePaymentId,
                fn (Builder $query): Builder => $query->whereKeyNot($excludePaymentId),
            )
            ->sum('amount'), 2);
    }

    public function pendingPaidForCompany(
        Company|int $company,
        ?int $excludePaymentId = null,
    ): float {
        $companyId = $company instanceof Company ? (int) $company->getKey() : $company;

        return round((float) PlatformCommissionPayment::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->when(
                $excludePaymentId,
                fn (Builder $query): Builder => $query->whereKeyNot($excludePaymentId),
            )
            ->sum('amount'), 2);
    }

    /**
     * كشف حساب الشركة مع المنصة.
     *
     * المستحق = عمولة 2٪ من دفعات طلبات الشركة.
     * المدفوع = دفعات الشركة المؤكدة للمنصة.
     * المتبقي = المستحق - المدفوع.
     */
    public function companyStatement(Company|int $company, bool $fresh = false): array
    {
        $companyId = $company instanceof Company ? (int) $company->getKey() : $company;

        if (! $fresh && isset($this->companyStatementCache[$companyId])) {
            return $this->companyStatementCache[$companyId];
        }

        $paidOrdersTotal = $this->paidOrdersTotalForCompany($companyId);
        $accrued = $this->calculateFromPaidAmount($paidOrdersTotal);
        $confirmed = $this->confirmedPaidForCompany($companyId);
        $pending = $this->pendingPaidForCompany($companyId);
        $remaining = round(max($accrued - $confirmed, 0), 2);
        $availableToSubmit = round(max($accrued - $confirmed - $pending, 0), 2);
        $overpaid = round(max($confirmed - $accrued, 0), 2);

        $status = match (true) {
            $overpaid > 0 => 'overpaid',
            $accrued <= 0 => 'no_due',
            $confirmed <= 0 => 'unpaid',
            $remaining > 0 => 'partial',
            default => 'paid',
        };

        $statement = [
            'company_id' => $companyId,
            'commission_rate' => $this->rate(),
            'commission_percentage' => $this->rate() * 100,
            'paid_orders_total' => $paidOrdersTotal,
            'accrued_commission' => $accrued,
            'confirmed_platform_payments' => $confirmed,
            'pending_platform_payments' => $pending,
            'remaining_amount' => $remaining,
            'available_to_submit' => $availableToSubmit,
            'overpaid_amount' => $overpaid,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
        ];

        return $this->companyStatementCache[$companyId] = $statement;
    }

    public function globalStatement(): array
    {
        $paidOrdersTotal = round((float) Payment::query()
            ->whereHas('order', fn (Builder $query): Builder =>
                $query->where('status', '!=', 'cancelled'))
            ->sum('amount'), 2);

        $accrued = $this->calculateFromPaidAmount($paidOrdersTotal);
        $confirmed = round((float) PlatformCommissionPayment::query()
            ->where('status', 'confirmed')
            ->sum('amount'), 2);
        $pending = round((float) PlatformCommissionPayment::query()
            ->where('status', 'pending')
            ->sum('amount'), 2);

        return [
            'paid_orders_total' => $paidOrdersTotal,
            'accrued_commission' => $accrued,
            'confirmed_platform_payments' => $confirmed,
            'pending_platform_payments' => $pending,
            'remaining_amount' => round(max($accrued - $confirmed, 0), 2),
            'overpaid_amount' => round(max($confirmed - $accrued, 0), 2),
        ];
    }

    public function forgetCompany(Company|int $company): void
    {
        $companyId = $company instanceof Company ? (int) $company->getKey() : $company;
        unset($this->companyStatementCache[$companyId]);
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'مسدد بالكامل',
            'partial' => 'مسدد جزئيًا',
            'unpaid' => 'غير مسدد',
            'overpaid' => 'رصيد زائد',
            default => 'لا يوجد مستحق',
        };
    }

    private function validOrderPaymentsForCompany(int $companyId): Builder
    {
        return Payment::query()
            ->whereHas('order', function (Builder $orderQuery) use ($companyId): void {
                $orderQuery
                    ->where('status', '!=', 'cancelled')
                    ->whereHas('productDetails', function (Builder $productQuery) use ($companyId): void {
                        $productQuery->where('product_details.company_id', $companyId);
                    });
            });
    }
}
