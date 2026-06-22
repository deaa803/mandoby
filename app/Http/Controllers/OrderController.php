<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        $orders = Order::with(['store', 'productDetails'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'date' => ['required', 'date'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],

            'products' => ['required', 'array', 'min:1'],
            'products.*.product_detail_id' => ['required', 'exists:product_details,id'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                $totalPrice = 0;

                foreach ($validated['products'] as $product) {
                    $price = $product['price'];
                    $quantity = $product['quantity'];
                    $discount = $product['discount'] ?? 0;

                    $totalPrice += ($price * $quantity) - $discount;
                }

                $commission = $validated['commission'] ?? 0;
                $paidAmount = $validated['paid_amount'] ?? 0;
                $remainingAmount = $totalPrice - $paidAmount;

                $order = Order::create([
                    'store_id' => $validated['store_id'],
                    'total_price' => $totalPrice,
                    'date' => $validated['date'],
                    'commission' => $commission,
                    'status' => $validated['status'] ?? 'pending',
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                ]);

                foreach ($validated['products'] as $product) {
                    $order->productDetails()->attach($product['product_detail_id'], [
                        'price' => $product['price'],
                        'quantity' => $product['quantity'],
                        'discount' => $product['discount'] ?? 0,
                    ]);
                }

                return $order;
            });

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['store', 'productDetails']),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully',
            'data' => $order->load(['store', 'productDetails']),
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'store_id' => ['sometimes', 'required', 'exists:stores,id'],
            'date' => ['sometimes', 'required', 'date'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],

            'products' => ['nullable', 'array', 'min:1'],
            'products.*.product_detail_id' => ['required_with:products', 'exists:product_details,id'],
            'products.*.price' => ['required_with:products', 'numeric', 'min:0'],
            'products.*.quantity' => ['required_with:products', 'integer', 'min:1'],
            'products.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $updatedOrder = DB::transaction(function () use ($validated, $order) {
                $orderData = [];

                if (array_key_exists('store_id', $validated)) {
                    $orderData['store_id'] = $validated['store_id'];
                }

                if (array_key_exists('date', $validated)) {
                    $orderData['date'] = $validated['date'];
                }

                if (array_key_exists('commission', $validated)) {
                    $orderData['commission'] = $validated['commission'] ?? 0;
                }

                if (array_key_exists('status', $validated)) {
                    $orderData['status'] = $validated['status'];
                }

                if (array_key_exists('paid_amount', $validated)) {
                    $orderData['paid_amount'] = $validated['paid_amount'] ?? 0;
                }

                if (array_key_exists('products', $validated)) {
                    $totalPrice = 0;
                    $syncData = [];

                    foreach ($validated['products'] as $product) {
                        $price = $product['price'];
                        $quantity = $product['quantity'];
                        $discount = $product['discount'] ?? 0;

                        $totalPrice += ($price * $quantity) - $discount;

                        $syncData[$product['product_detail_id']] = [
                            'price' => $price,
                            'quantity' => $quantity,
                            'discount' => $discount,
                        ];
                    }

                    $orderData['total_price'] = $totalPrice;

                    $paidAmount = $orderData['paid_amount'] ?? $order->paid_amount;
                    $orderData['remaining_amount'] = $totalPrice - $paidAmount;

                    $order->productDetails()->sync($syncData);
                } else {
                    if (array_key_exists('paid_amount', $orderData)) {
                        $orderData['remaining_amount'] = $order->total_price - $orderData['paid_amount'];
                    }
                }

                if (!empty($orderData)) {
                    $order->update($orderData);
                }

                return $order->fresh()->load(['store', 'productDetails']);
            });

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully',
                'data' => $updatedOrder,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                $order->productDetails()->detach();
                $order->delete();
            });

            return response()->json([
                'status' => true,
                'message' => 'Order deleted successfully',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
