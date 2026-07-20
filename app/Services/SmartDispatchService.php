<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class SmartDispatchService
{
    public const SAFETY_MARGIN_PERCENT = 10;

    private const ACTIVE_ORDER_STATUSES = [
        'pending',
        'preparing',
        'delivering',
    ];

    /**
     * Recalculate and optionally persist the order weight using the quantity
     * of parcels and the package weight snapshot on every order line.
     */
    public function calculateOrderWeight(Order $order, bool $persist = true): array
    {
        $order->loadMissing('productDetails.product');

        $missingProducts = [];
        $lines = [];
        $totalWeight = 0.0;

        foreach ($order->productDetails as $productDetail) {
            $quantity = (int) $productDetail->pivot->quantity;
            $snapshotWeight = (float) ($productDetail->pivot->package_weight_kg ?? 0);
            $currentWeight = (float) ($productDetail->package_weight_kg ?? 0);
            $packageWeight = $snapshotWeight > 0 ? $snapshotWeight : $currentWeight;

            if ($packageWeight <= 0) {
                $missingProducts[] = [
                    'product_detail_id' => $productDetail->id,
                    'product_name' => $productDetail->product?->name ?? "#{$productDetail->id}",
                ];

                continue;
            }

            $lineWeight = round($packageWeight * $quantity, 3);
            $totalWeight += $lineWeight;

            $lines[] = [
                'product_detail_id' => $productDetail->id,
                'product_name' => $productDetail->product?->name,
                'quantity' => $quantity,
                'package_weight_kg' => round($packageWeight, 3),
                'line_weight_kg' => $lineWeight,
            ];

            if ($persist) {
                $order->productDetails()->updateExistingPivot($productDetail->id, [
                    'package_weight_kg' => round($packageWeight, 3),
                    'line_weight_kg' => $lineWeight,
                ]);
            }
        }

        $totalWeight = round($totalWeight, 3);
        $requiredLoad = round(
            $totalWeight * (1 + (self::SAFETY_MARGIN_PERCENT / 100)),
            3,
        );

        if ($persist && $missingProducts === []) {
            $order->update([
                'total_weight_kg' => $totalWeight,
                'required_load_kg' => $requiredLoad,
            ]);
        }

        return [
            'ready' => $missingProducts === [],
            'safety_margin_percent' => self::SAFETY_MARGIN_PERCENT,
            'total_weight_kg' => $totalWeight,
            'required_load_kg' => $requiredLoad,
            'lines' => $lines,
            'missing_products' => $missingProducts,
        ];
    }

    public function recommendations(Order $order): array
    {
        $order->loadMissing([
            'store.user',
            'productDetails.product',
            'productDetails.company',
        ]);

        $companyId = $this->companyIdForOrder($order);
        $weight = $this->calculateOrderWeight($order);

        if (! $weight['ready']) {
            return [
                'ready' => false,
                'message' => 'لا يمكن اقتراح سائق قبل إدخال وزن الطرد لجميع منتجات الطلب.',
                'company_id' => $companyId,
                'weight' => $weight,
                'recommendations' => [],
            ];
        }

        $requiredLoad = (float) $weight['required_load_kg'];

        $drivers = Driver::query()
            ->with(['user', 'car.company'])
            ->withCount([
                'orders as active_orders_count' => fn ($query) => $query
                    ->whereIn('status', self::ACTIVE_ORDER_STATUSES),
            ])
            ->whereHas('car', function ($query) use ($companyId, $requiredLoad): void {
                $query
                    ->where('company_id', $companyId)
                    ->whereNotNull('max_load_kg')
                    ->where('max_load_kg', '>=', $requiredLoad);
            })
            ->where(function ($query) use ($order): void {
                $query->where('status', 'available');

                if ($order->driver_id) {
                    $query->orWhereKey($order->driver_id);
                }
            })
            ->get();

        $recommendations = $drivers
            ->map(fn (Driver $driver): array => $this->scoreDriver($order, $driver, $requiredLoad))
            ->sortByDesc('score')
            ->values()
            ->all();

        return [
            'ready' => true,
            'message' => $recommendations === []
                ? 'لا يوجد سائق متاح بسيارة تستوعب وزن الطلب حاليًا.'
                : 'تم ترتيب السائقين حسب ملاءمة حمولة السيارة والقرب وضغط العمل.',
            'company_id' => $companyId,
            'weight' => $weight,
            'recommendations' => $recommendations,
        ];
    }

    public function validateDriverForOrder(Order $order, Driver $driver): array
    {
        $order->loadMissing('productDetails.product');
        $driver->loadMissing(['user', 'car.company']);

        $companyId = $this->companyIdForOrder($order);
        $weight = $this->calculateOrderWeight($order);

        if (! $weight['ready']) {
            throw ValidationException::withMessages([
                'driver_id' => 'لا يمكن تعيين سائق لأن وزن الطرد غير مسجل لبعض منتجات الطلب.',
            ]);
        }

        if (! $driver->car || (int) $driver->car->company_id !== $companyId) {
            throw ValidationException::withMessages([
                'driver_id' => 'السائق المحدد لا يتبع للشركة صاحبة الطلب.',
            ]);
        }

        if ((float) $driver->car->max_load_kg <= 0) {
            throw ValidationException::withMessages([
                'driver_id' => 'حمولة سيارة السائق غير مسجلة.',
            ]);
        }

        if ((float) $driver->car->max_load_kg < (float) $weight['required_load_kg']) {
            throw ValidationException::withMessages([
                'driver_id' => 'حمولة سيارة السائق أقل من الحمولة المطلوبة للطلب.',
            ]);
        }

        if ($driver->status !== 'available' && (int) $order->driver_id !== (int) $driver->id) {
            throw ValidationException::withMessages([
                'driver_id' => 'السائق المحدد غير متاح حاليًا.',
            ]);
        }

        return [
            'company_id' => $companyId,
            'weight' => $weight,
            'driver' => $this->scoreDriver(
                $order,
                $driver,
                (float) $weight['required_load_kg'],
            ),
        ];
    }

    public function companyIdForOrder(Order $order): int
    {
        $companyIds = $order->productDetails()
            ->pluck('product_details.company_id')
            ->unique()
            ->values();

        if ($companyIds->count() !== 1) {
            throw ValidationException::withMessages([
                'order' => 'يجب أن يحتوي الطلب على منتجات تابعة لشركة واحدة فقط.',
            ]);
        }

        return (int) $companyIds->first();
    }

    private function scoreDriver(Order $order, Driver $driver, float $requiredLoad): array
    {
        $maxLoad = (float) $driver->car->max_load_kg;
        $utilization = $maxLoad > 0
            ? round(($requiredLoad / $maxLoad) * 100, 2)
            : 0.0;

        // The smallest sufficient car gets the highest capacity score.
        $capacityScore = round(min(50, max(0, $utilization * 0.50)), 2);

        $distanceKm = $this->distanceToStoreKm($order, $driver);
        $distanceScore = $distanceKm === null
            ? 12.5
            : round(max(0, 25 - min(25, $distanceKm)), 2);

        $activeOrders = (int) ($driver->active_orders_count
            ?? $driver->orders()->whereIn('status', self::ACTIVE_ORDER_STATUSES)->count());

        $workloadScore = max(0, 15 - ($activeOrders * 5));
        $availabilityScore = $driver->status === 'available' ? 10 : 5;

        $score = round(
            $capacityScore + $distanceScore + $workloadScore + $availabilityScore,
            2,
        );

        $reasons = [
            'السائق يتبع لنفس الشركة.',
            'حمولة السيارة كافية للطلب.',
            'نسبة استغلال الحمولة ' . number_format($utilization, 1) . '٪.',
            $activeOrders === 0
                ? 'لا توجد طلبات نشطة على السائق.'
                : "لدى السائق {$activeOrders} طلب نشط.",
        ];

        if ($distanceKm !== null) {
            $reasons[] = 'يبعد عن المتجر تقريبًا ' . number_format($distanceKm, 1) . ' كم.';
        }

        return [
            'driver_id' => $driver->id,
            'driver_name' => $driver->user?->name,
            'driver_status' => $driver->status,
            'car_id' => $driver->car->id,
            'vehicle_type' => $driver->car->vehicle_type,
            'plate_number' => $driver->car->plate_number,
            'max_load_kg' => round($maxLoad, 2),
            'required_load_kg' => round($requiredLoad, 3),
            'capacity_utilization_percent' => $utilization,
            'active_orders_count' => $activeOrders,
            'distance_to_store_km' => $distanceKm,
            'score' => $score,
            'reasons' => $reasons,
        ];
    }

    private function distanceToStoreKm(Order $order, Driver $driver): ?float
    {
        $storeUser = $order->store?->user;

        if (
            $driver->current_lat === null
            || $driver->current_lng === null
            || $storeUser?->latitude === null
            || $storeUser?->longitude === null
        ) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad((float) $driver->current_lat);
        $lonFrom = deg2rad((float) $driver->current_lng);
        $latTo = deg2rad((float) $storeUser->latitude);
        $lonTo = deg2rad((float) $storeUser->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        return round(2 * $earthRadius * asin(min(1, sqrt($a))), 2);
    }
}
