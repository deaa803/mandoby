<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Filament\Resources\Advertisements\AdvertisementResource;
use App\Models\ProductDetail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->validateSelectedProductCompany($data);

        return $data;
    }

    private function validateSelectedProductCompany(array $data): void
    {
        $productDetailId = $data['product_detail_id'] ?? null;
        $companyId = $data['company_id'] ?? null;

        if (blank($productDetailId)) {
            return;
        }

        $belongsToCompany = ProductDetail::query()
            ->whereKey($productDetailId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $belongsToCompany) {
            throw ValidationException::withMessages([
                'data.product_detail_id' => 'المنتج المختار لا يتبع للشركة المحددة. اختر منتجًا من منتجات الشركة نفسها.',
            ]);
        }
    }
}
