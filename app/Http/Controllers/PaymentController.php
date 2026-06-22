<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $payments = Payment::with('order')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Payments retrieved successfully',
            'data' => $payments,
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        try {
            $payment = DB::transaction(function () use ($validated) {
                $order = Order::lockForUpdate()->findOrFail($validated['order_id']);

                $currentPaidAmount = $order->payments()->sum('amount');
                $newPaidAmount = $currentPaidAmount + $validated['amount'];

                if ($newPaidAmount > $order->total_price) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount exceeds the remaining order amount.',
                    ]);
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $validated['amount'],
                    'paid_at' => $validated['paid_at'],
                    'note' => $validated['note'] ?? null,
                ]);

                $this->syncOrderPaymentAmounts($order);

                return $payment;
            });

            return response()->json([
                'status' => true,
                'message' => 'Payment created successfully',
                'data' => $payment->load('order'),
            ], 201);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        return response()->json([
            'status' => true,
            'message' => 'Payment retrieved successfully',
            'data' => $payment->load('order'),
        ]);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'paid_at' => ['sometimes', 'required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        try {
            $updatedPayment = DB::transaction(function () use ($validated, $payment) {
                $order = Order::lockForUpdate()->findOrFail($payment->order_id);

                $amountAfterUpdate = array_key_exists('amount', $validated)
                    ? $validated['amount']
                    : $payment->amount;

                $paidAmountWithoutThisPayment = $order->payments()
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                $newPaidAmount = $paidAmountWithoutThisPayment + $amountAfterUpdate;

                if ($newPaidAmount > $order->total_price) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount exceeds the remaining order amount.',
                    ]);
                }

                $paymentData = [];

                if (array_key_exists('amount', $validated)) {
                    $paymentData['amount'] = $validated['amount'];
                }

                if (array_key_exists('paid_at', $validated)) {
                    $paymentData['paid_at'] = $validated['paid_at'];
                }

                if (array_key_exists('note', $validated)) {
                    $paymentData['note'] = $validated['note'];
                }

                if (!empty($paymentData)) {
                    $payment->update($paymentData);
                }

                $this->syncOrderPaymentAmounts($order);

                return $payment->fresh()->load('order');
            });

            return response()->json([
                'status' => true,
                'message' => 'Payment updated successfully',
                'data' => $updatedPayment,
            ]);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified payment.
     */
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
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate paid_amount and remaining_amount for the order.
     */
    private function syncOrderPaymentAmounts(Order $order): void
    {
        $order->refresh();

        $paidAmount = $order->payments()->sum('amount');
        $remainingAmount = max($order->total_price - $paidAmount, 0);

        $order->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
        ]);
    }
}
