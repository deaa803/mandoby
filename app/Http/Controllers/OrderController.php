<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use App\Models\ProductDetail;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private array $relations = [
        'store.user',
        'driver.user',
        'driver.car.company',
        'productDetails.product',
        'productDetails.company',
        'productDetails.category',
        'productDetails.images',
        'payments',
    ];

    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Orders retrieved successfully',
            'data' => Order::with($this->relations)->latest()->get(),
        ]);
    }

    /**
     * Create one order. All submitted products must belong to one company.
     * Flutter normally uses storeBatch() so a multi-company cart is split
     * into one order per company inside one database transaction.
     */
    public function store(Request $request)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        $validated = $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_detail_id' => [
                'required',
                'integer',
                'distinct',
                'exists:product_details,id',
            ],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = DB::transaction(function () use ($validated, $store) {
                $prepared = $this->prepareProducts($validated['products']);

                if ($prepared['company_ids']->count() !== 1) {
                    throw ValidationException::withMessages([
                        'products' => 'All products in one order must belong to the same company.',
                    ]);
                }

                return $this->createOrder(
                    storeId: $store->id,
                    lines: $prepared['lines'],
                );
            });

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'data' => $order->load($this->relations),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Failed to create order', $e);
        }
    }

    /**
     * Create a separate order for every company represented in the cart.
     * The whole operation succeeds or fails together.
     */
    public function storeBatch(Request $request)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        $validated = $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_detail_id' => [
                'required',
                'integer',
                'distinct',
                'exists:product_details,id',
            ],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $orders = DB::transaction(function () use ($validated, $store) {
                $prepared = $this->prepareProducts($validated['products']);

                return $prepared['lines']
                    ->groupBy('company_id')
                    ->map(function (Collection $companyLines) use ($store) {
                        return $this->createOrder(
                            storeId: $store->id,
                            lines: $companyLines,
                        );
                    })
                    ->values();
            });

            $orders->each->load($this->relations);

            return response()->json([
                'status' => true,
                'message' => 'Orders created successfully',
                'data' => [
                    'orders_count' => $orders->count(),
                    'orders' => $orders,
                ],
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Failed to create orders', $e);
        }
    }

    public function show(Order $order)
    {
        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully',
            'data' => $order->load($this->relations),
        ]);
    }

    public function showStoreOrder(Request $request, Order $order)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        if ((int) $order->store_id !== (int) $store->id) {
            return response()->json([
                'status' => false,
                'message' => 'This order does not belong to your store',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Store order retrieved successfully',
            'data' => $order->load($this->relations),
        ]);
    }

    public function showCompanyOrder(Request $request, Order $order)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        if (!$this->orderBelongsToCompany($order, $company->id)) {
            return response()->json([
                'status' => false,
                'message' => 'This order does not belong to your company',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Company order retrieved successfully',
            'data' => $order->load($this->relations),
        ]);
    }

    /**
     * Administrative update. Product prices and discounts are always
     * recalculated from Laravel and never trusted from the request.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'date' => ['sometimes', 'date'],
            'commission' => ['sometimes', 'numeric', 'min:0'],
            'status' => [
                'sometimes',
                'in:pending,preparing,delivering,delivered,cancelled',
            ],
            'driver_id' => ['sometimes', 'nullable', 'exists:drivers,id'],
            'products' => ['sometimes', 'array', 'min:1'],
            'products.*.product_detail_id' => [
                'required_with:products',
                'integer',
                'distinct',
                'exists:product_details,id',
            ],
            'products.*.quantity' => [
                'required_with:products',
                'integer',
                'min:1',
            ],
        ]);

        try {
            $updated = DB::transaction(function () use ($validated, $order) {
                $orderData = collect($validated)
                    ->only(['date', 'commission', 'status', 'driver_id'])
                    ->toArray();

                if (array_key_exists('products', $validated)) {
                    $prepared = $this->prepareProducts($validated['products']);

                    if ($prepared['company_ids']->count() !== 1) {
                        throw ValidationException::withMessages([
                            'products' => 'All products in one order must belong to the same company.',
                        ]);
                    }

                    $total = $prepared['lines']->sum('line_total');
                    $orderData['total_price'] = $total;
                    $orderData['remaining_amount'] = max(
                        $total - (float) $order->paid_amount,
                        0,
                    );

                    $sync = [];
                    foreach ($prepared['lines'] as $line) {
                        $sync[$line['product_detail_id']] = [
                            'price' => $line['price'],
                            'quantity' => $line['quantity'],
                            'discount' => $line['discount_amount'],
                        ];
                    }

                    $order->productDetails()->sync($sync);
                }

                if ($orderData !== []) {
                    $order->update($orderData);
                }

                return $order->fresh()->load($this->relations);
            });

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully',
                'data' => $updated,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Failed to update order', $e);
        }
    }

    public function destroy(Order $order)
    {
        try {
            $order->delete();

            return response()->json([
                'status' => true,
                'message' => 'Order deleted successfully',
                'data' => null,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to delete order', $e);
        }
    }

    public function myOrders(Request $request)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        return response()->json([
            'status' => true,
            'message' => 'My orders retrieved successfully',
            'data' => Order::with($this->relations)
                ->where('store_id', $store->id)
                ->latest()
                ->get(),
        ]);
    }

    public function myCurrentOrders(Request $request)
    {
        return $this->myOrdersByCompletion($request, false);
    }

    public function myCompletedOrders(Request $request)
    {
        return $this->myOrdersByCompletion($request, true);
    }

    public function myDebts(Request $request)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        $orders = Order::with($this->relations)
            ->where('store_id', $store->id)
            ->where('remaining_amount', '>', 0)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'My debts retrieved successfully',
            'data' => [
                'summary' => [
                    'orders_count' => $orders->count(),
                    'total_sales' => (float) $orders->sum('total_price'),
                    'total_paid' => (float) $orders->sum('paid_amount'),
                    'total_remaining' => (float) $orders->sum('remaining_amount'),
                ],
                'orders' => $orders,
            ],
        ]);
    }

    public function companyOrders(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        return response()->json([
            'status' => true,
            'message' => 'Company orders retrieved successfully',
            'data' => $this->companyOrdersQuery($company->id)
                ->latest()
                ->get(),
        ]);
    }

    public function companyReceivables(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        $allOrders = $this->companyOrdersQuery($company->id)
            ->latest()
            ->get();

        $receivableOrders = $allOrders
            ->where('remaining_amount', '>', 0)
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Company receivables retrieved successfully',
            'data' => [
                'summary' => [
                    'orders_count' => $allOrders->count(),
                    'unpaid_orders_count' => $receivableOrders->count(),
                    'total_sales' => (float) $allOrders->sum('total_price'),
                    'total_paid' => (float) $allOrders->sum('paid_amount'),
                    'total_remaining' => (float) $allOrders->sum('remaining_amount'),
                ],
                'orders' => $receivableOrders,
            ],
        ]);
    }

    public function assignDriver(
        Request $request,
        Order $order,
        FirebaseNotificationService $fcmService,
    ) {
        $company = $request->user()?->company;

        if (!$company) {
            return $this->notFound('Company account not found');
        }

        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
        ]);

        if (!$this->orderBelongsToCompany($order, $company->id)) {
            return response()->json([
                'status' => false,
                'message' => 'This order does not belong to your company',
                'data' => null,
            ], 403);
        }

        if (in_array($order->status, ['delivered', 'cancelled'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Delivered or cancelled orders cannot be assigned to a driver',
                'data' => null,
            ], 422);
        }

        $driver = Driver::with(['user', 'car.company'])
            ->whereKey($validated['driver_id'])
            ->whereHas('car', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->first();

        if (!$driver) {
            return $this->notFound('Driver not found in your company');
        }

        if ($driver->status !== 'available' && (int) $order->driver_id !== $driver->id) {
            throw ValidationException::withMessages([
                'driver_id' => 'This driver is not available right now.',
            ]);
        }

        DB::transaction(function () use ($order, $driver) {
            if ($order->driver_id && (int) $order->driver_id !== $driver->id) {
                Driver::whereKey($order->driver_id)->update(['status' => 'available']);
            }

            $order->update([
                'driver_id' => $driver->id,
                'status' => 'delivering',
            ]);

            $driver->update(['status' => 'busy']);
        });

        $order->load($this->relations);

        $pushError = null;

        if ($driver->fcm_token) {
            try {
                $fcmService->sendToToken(
                    token: $driver->fcm_token,
                    title: 'طلب توصيل جديد',
                    body: 'تم إسناد طلب جديد إليك',
                    data: [
                        'type' => 'new_order',
                        'order_id' => (string) $order->id,
                        'driver_id' => (string) $driver->id,
                    ],
                );
            } catch (\Throwable $e) {
                $pushError = $e->getMessage();
            }
        }

        return response()->json([
            'status' => true,
            'message' => $pushError
                ? 'Order assigned, but push notification failed'
                : 'Order assigned to driver successfully',
            'data' => [
                'order' => $order,
                'push_error' => $pushError,
            ],
        ]);
    }

    private function myOrdersByCompletion(Request $request, bool $completed)
    {
        $store = $request->user()?->store;

        if (!$store) {
            return $this->notFound('Store account not found');
        }

        $query = Order::with($this->relations)
            ->where('store_id', $store->id);

        $completed
            ? $query->where('status', 'delivered')
            : $query->whereNotIn('status', ['delivered', 'cancelled']);

        return response()->json([
            'status' => true,
            'message' => $completed
                ? 'Completed orders retrieved successfully'
                : 'Current orders retrieved successfully',
            'data' => $query->latest()->get(),
        ]);
    }

    private function prepareProducts(array $products): array
    {
        $requested = collect($products)->keyBy('product_detail_id');
        $details = ProductDetail::query()
            ->with(['product', 'company'])
            ->whereIn('id', $requested->keys())
            ->sharedLock()
            ->get()
            ->keyBy('id');

        if ($details->count() !== $requested->count()) {
            throw ValidationException::withMessages([
                'products' => 'One or more products were not found.',
            ]);
        }

        $lines = $requested->map(function (array $requestedLine, int|string $id) use ($details) {
            /** @var ProductDetail $detail */
            $detail = $details->get((int) $id);
            $quantity = (int) $requestedLine['quantity'];

            if ($detail->status !== 'available') {
                throw ValidationException::withMessages([
                    'products' => "Product detail #{$detail->id} is unavailable.",
                ]);
            }

            if ($quantity < (int) $detail->min_order_quantity) {
                throw ValidationException::withMessages([
                    'products' => "Minimum quantity for product detail #{$detail->id} is {$detail->min_order_quantity}.",
                ]);
            }

            $price = (float) $detail->price;
            $gross = round($price * $quantity, 2);
            $discountPercent = $this->discountPercentageFor($quantity);
            $discountAmount = round($gross * ($discountPercent / 100), 2);

            return [
                'product_detail_id' => $detail->id,
                'company_id' => $detail->company_id,
                'price' => $price,
                'quantity' => $quantity,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'line_total' => round($gross - $discountAmount, 2),
            ];
        })->values();

        return [
            'lines' => $lines,
            'company_ids' => $lines->pluck('company_id')->unique()->values(),
        ];
    }

    private function createOrder(int $storeId, Collection $lines): Order
    {
        $total = round((float) $lines->sum('line_total'), 2);

        $order = Order::create([
            'store_id' => $storeId,
            'driver_id' => null,
            'total_price' => $total,
            'date' => now()->toDateString(),
            'commission' => 0,
            'status' => 'pending',
            'paid_amount' => 0,
            'remaining_amount' => $total,
        ]);

        $attach = [];

        foreach ($lines as $line) {
            $attach[$line['product_detail_id']] = [
                'price' => $line['price'],
                'quantity' => $line['quantity'],
                'discount' => $line['discount_amount'],
            ];
        }

        $order->productDetails()->attach($attach);

        return $order;
    }

    private function discountPercentageFor(int $quantity): float
    {
        return match (true) {
            $quantity >= 50 => 7,
            $quantity >= 25 => 5,
            $quantity >= 10 => 2,
            default => 0,
        };
    }

    private function companyOrdersQuery(int $companyId)
    {
        return Order::with($this->relations)
            ->whereHas('productDetails', function ($query) use ($companyId) {
                $query->where('product_details.company_id', $companyId);
            });
    }

    private function orderBelongsToCompany(Order $order, int $companyId): bool
    {
        return $order->productDetails()
            ->where('product_details.company_id', $companyId)
            ->exists();
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
