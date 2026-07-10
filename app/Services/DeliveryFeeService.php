<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Store;

class DeliveryFeeService
{
    public function calculateByIds(int $storeId, int $companyId): array
    {
        $store = Store::with('user')->find($storeId);
        $company = Company::with('user')->find($companyId);

        if (! $store || ! $company) {
            return $this->emptyResult();
        }

        return $this->calculate($store, $company);
    }

    public function calculate(Store $store, Company $company): array
    {
        $storeLat = $this->toFloat($store->user?->latitude);
        $storeLng = $this->toFloat($store->user?->longitude);
        $companyLat = $this->toFloat($company->user?->latitude);
        $companyLng = $this->toFloat($company->user?->longitude);

        if (! $this->hasCoordinates($storeLat, $storeLng, $companyLat, $companyLng)) {
            return $this->emptyResult();
        }

        $distanceKm = round(
            $this->distanceKm($companyLat, $companyLng, $storeLat, $storeLng),
            2,
        );

        $freeRadiusKm = max(0, (float) $company->delivery_radius_km);
        $feePerKm = max(0, (float) $company->extra_delivery_fee_per_km);
        $extraKm = (int) ceil(max(0, $distanceKm - $freeRadiusKm));
        $extraFee = round($extraKm * $feePerKm, 2);

        return [
            'delivery_distance_km' => $distanceKm,
            'delivery_radius_km' => $freeRadiusKm,
            'extra_delivery_km' => $extraKm,
            'extra_delivery_fee_per_km' => $feePerKm,
            'extra_delivery_fee' => $extraFee,
        ];
    }

    private function emptyResult(): array
    {
        return [
            'delivery_distance_km' => 0,
            'delivery_radius_km' => 0,
            'extra_delivery_km' => 0,
            'extra_delivery_fee_per_km' => 0,
            'extra_delivery_fee' => 0,
        ];
    }

    private function hasCoordinates(?float ...$values): bool
    {
        foreach ($values as $value) {
            if ($value === null) {
                return false;
            }
        }

        return true;
    }

    private function toFloat(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
