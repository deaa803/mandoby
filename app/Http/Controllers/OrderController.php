<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    private array $relations = [
        'store.user',
        'productDetails.product',
        'productDetails.company',
        'productDetails.category',
        'productDetails.images',
        'payments',
    ];

    /**
     * Display a listing of orders.
     */
    public function index()
    {
        $orders = Order::with($this->relations)
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
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'status' => false,
                'message' => 'Store account not found',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
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
            $order = DB::transaction(function () use ($validated, $store) {
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
                    'store_id' => $store->id,
                    'total_price' => $totalPrice,
                    'date' => $validated['date'] ?? now()->toDateString(),
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
                'data' => $order->load($this->relations),
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
            'data' => $order->load($this->relations),
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
                $orderData = collect($validated)
                    ->only(['store_id', 'date', 'commission', 'status', 'paid_amount'])
                    ->toArray();

                if (array_key_exists('commission', $orderData)) {
                    $orderData['commission'] = $orderData['commission'] ?? 0;
                }

                if (array_key_exists('paid_amount', $orderData)) {
                    $orderData['paid_amount'] = $orderData['paid_amount'] ?? 0;
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
                } elseif (array_key_exists('paid_amount', $orderData)) {
                    $orderData['remaining_amount'] = $order->total_price - $orderData['paid_amount'];
                }

                if (!empty($orderData)) {
                    $order->update($orderData);
                }

                return $order->fresh()->load($this->relations);
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
                'data' => null,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function myOrders(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'status' => false,
                'message' => 'Store account not found',
                'data' => null,
            ], 404);
        }

        $orders = Order::with($this->relations)
            ->where('store_id', $store->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'My orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function myDebts(Request $request)
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json([
                'status' => false,
                'message' => 'Store account not found',
                'data' => null,
            ], 404);
        }

        $orders = Order::with($this->relations)
            ->where('store_id', $store->id)
            ->where('remaining_amount', '>', 0)
            ->latest()
            ->get();

        $totalDebt = $orders->sum('remaining_amount');

        return response()->json([
            'status' => true,
            'message' => 'My debts retrieved successfully',
            'data' => [
                'total_debt' => $totalDebt,
                'orders' => $orders,
            ],
        ]);
    }

    public function companyOrders(Request $request)
    {
        $company = $request->user()->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        $orders = Order::with($this->relations)
            ->whereHas('productDetails', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Company orders retrieved successfully',
            'data' => $orders,
        ]);
    }
    public function assignDriver(Request $request, Order $order, FcmService $fcmService)
    {
        $user = $request->user()->load('company');

        if ($user->user_type !== 'company') {
            return response()->json([
                'status' => false,
                'message' => 'This account is not a company account',
            ], 403);
        }

        if (!$user->company) {
            return response()->json([
                'status' => false,
                'message' => 'Company profile not found',
            ], 404);
        }

        $validated = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
        ]);

        $driver = Driver::where('id', $validated['driver_id'])
            ->where('company_id', $user->company->id)
            ->first();

        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not found in your company',
            ], 404);
        }

        $belongsToCompany = $order->productDetails()
            ->where('company_id', $user->company->id)
            ->exists();

        if (!$belongsToCompany) {
            return response()->json([
                'status' => false,
                'message' => 'This order does not belong to your company',
            ], 403);
        }

        $order->update([
            'driver_id' => $driver->id,
            'status' => 'delivering',
        ]);

        $order->load([
            'store.user',
            'driver.user',
            'driver.company',
            'productDetails.product',
            'productDetails.category',
            'productDetails.images',
        ]);

        if ($driver->fcm_token) {
            try {
                $fcmService->sendToToken(
                    token: $driver->fcm_token,
                    title: 'طلب جديد',
                    body: 'لديك طلب توصيل جديد',
                    data: [
                        'type' => 'new_order',
                        'order_id' => $order->id,
                        'driver_id' => $driver->id,
                    ],
                );
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => true,
                    'message' => 'Order assigned to driver, but push notification failed',
                    'data' => [
                        'order' => $order,
                        'push_error' => $e->getMessage(),
                    ],
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Order assigned to driver successfully',
            'data' => [
                'order' => $order,
            ],
        ]);
    }
}
