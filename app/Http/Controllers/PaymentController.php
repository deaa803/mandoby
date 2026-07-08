<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    private array $orderRelations = [
        'store.user',
        'driver.user',
        'driver.car.company',
        'productDetails.product',
        'productDetails.company',
        'payments',
    ];

    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Payments retrieved successfully',
            'data' => Payment::with('order')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        return $this->createPayment(
            orderId: (int) $validated['order_id'],
            amount: (float) $validated['amount'],
            paidAt: $validated['paid_at'] ?? now(),
            note: $validated['note'] ?? null,
        );
    }

    public function companyStorePayment(Request $request, Order $order)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        $belongsToCompany = $order->productDetails()
            ->where('product_details.company_id', $company->id)
            ->exists();

        if (!$belongsToCompany) {
            return response()->json([
                'status' => false,
                'message' => 'This order does not belong to your company',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        return $this->createPayment(
            orderId: $order->id,
            amount: (float) $validated['amount'],
            paidAt: $validated['paid_at'] ?? now(),
            note: $validated['note'] ?? null,
        );
    }

    public function show(Payment $payment)
    {
        return response()->json([
            'status' => true,
            'message' => 'Payment retrieved successfully',
            'data' => $payment->load('order'),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'paid_at' => ['sometimes', 'date'],
            'note' => ['sometimes', 'nullable', 'string'],
        ]);

        try {
            $updated = DB::transaction(function () use ($validated, $payment) {
                $order = Order::lockForUpdate()->findOrFail($payment->order_id);
                $newAmount = (float) ($validated['amount'] ?? $payment->amount);
                $otherPayments = (float) $order->payments()
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                if (($otherPayments + $newAmount) > (float) $order->total_price) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount exceeds the remaining order amount.',
                    ]);
                }

                $payment->update($validated);
                $this->syncOrderPaymentAmounts($order);

                return $payment->fresh()->load('order');
            });

            return response()->json([
                'status' => true,
                'message' => 'Payment updated successfully',
                'data' => $updated,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Failed to update payment', $e);
        }
    }

    public function destroy(Payment $payment)
    {
        try {
            DB::transaction(function () use ($payment) {
                $order = Order::lockForUpdate()->findOrFail($payment->order_id);
                $payment->delete();
                $this->syncOrderPaymentAmounts($order);
            });

            return response()->json([
                'status' => true,
                'message' => 'Payment deleted successfully',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to delete payment', $e);
        }
    }

    public function storePayments(Request $request)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        $payments = Payment::with(['order' => fn ($query) => $query->with($this->orderRelations)])
            ->whereHas('order', fn ($query) => $query->where('store_id', $store->id))
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Store payments retrieved successfully',
            'data' => $payments,
        ]);
    }

    public function companyPayments(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        $payments = Payment::with(['order' => fn ($query) => $query->with($this->orderRelations)])
            ->whereHas('order.productDetails', function ($query) use ($company) {
                $query->where('product_details.company_id', $company->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Company payments retrieved successfully',
            'data' => $payments,
        ]);
    }

    public function storeInstallments(Request $request)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        $orders = Order::with($this->orderRelations)
            ->where('store_id', $store->id)
            ->where('remaining_amount', '>', 0)
            ->latest()
            ->get();

        return $this->installmentsResponse(
            orders: $orders,
            message: 'Store installments retrieved successfully',
            titlePrefix: 'قسط طلب',
        );
    }

    public function companyInstallments(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        $orders = Order::with($this->orderRelations)
            ->whereHas('productDetails', function ($query) use ($company) {
                $query->where('product_details.company_id', $company->id);
            })
            ->where('remaining_amount', '>', 0)
            ->latest()
            ->get();

        return $this->installmentsResponse(
            orders: $orders,
            message: 'Company installments retrieved successfully',
            titlePrefix: 'مستحقات الطلب',
        );
    }

    private function createPayment(
        int $orderId,
        float $amount,
        mixed $paidAt,
        ?string $note,
    ) {
        try {
            $payment = DB::transaction(function () use (
                $orderId,
                $amount,
                $paidAt,
                $note,
            ) {
                $order = Order::lockForUpdate()->findOrFail($orderId);
                $remaining = max(
                    (float) $order->total_price - (float) $order->payments()->sum('amount'),
                    0,
                );

                if ($amount > $remaining) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount exceeds the remaining order amount.',
                    ]);
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'paid_at' => $paidAt,
                    'note' => $note,
                ]);

                $this->syncOrderPaymentAmounts($order);

                return $payment->load('order');
            });

            return response()->json([
                'status' => true,
                'message' => 'Payment created successfully',
                'data' => $payment,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Failed to create payment', $e);
        }
    }

    private function installmentsResponse($orders, string $message, string $titlePrefix)
    {
        $installments = $orders->map(function (Order $order) use ($titlePrefix) {
            return [
                'id' => $order->id,
                'order_id' => $order->id,
                'title' => "{$titlePrefix} #{$order->id}",
                'status' => 'مستحقة',
                'amount' => (float) $order->remaining_amount,
                'paid_amount' => (float) $order->paid_amount,
                'remaining_amount' => (float) $order->remaining_amount,
                'total_price' => (float) $order->total_price,
                'due_date' => $order->date?->copy()->addMonth()->toDateString(),
                'order' => $order,
            ];
        })->values();

        $payments = $orders
            ->flatMap(fn (Order $order) => $order->payments)
            ->sortByDesc('paid_at')
            ->values();

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => [
                'summary' => [
                    'total_orders' => $orders->count(),
                    'total_price' => (float) $orders->sum('total_price'),
                    'total_paid' => (float) $orders->sum('paid_amount'),
                    'total_remaining' => (float) $orders->sum('remaining_amount'),
                ],
                'installments' => $installments,
                'payments' => $payments,
                'orders' => $orders,
            ],
        ]);
    }

    private function syncOrderPaymentAmounts(Order $order): void
    {
        $paid = (float) $order->payments()->sum('amount');

        $order->update([
            'paid_amount' => $paid,
            'remaining_amount' => max((float) $order->total_price - $paid, 0),
        ]);
    }

    private function notFound(string $message)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
        ], 404);
    }

    private function serverError(string $message, \Throwable $e)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'error' => $e->getMessage(),
        ], 500);
    }
}
