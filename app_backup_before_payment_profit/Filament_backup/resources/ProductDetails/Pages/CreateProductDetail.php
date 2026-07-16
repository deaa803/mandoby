<?php

namespace App\Filament\Resources\ProductDetails\Pages;

use App\Filament\Resources\ProductDetails\ProductDetailResource;
use App\Models\ProductDetail;
use Filament\Resources\Pages\CreateRecord;

class CreateProductDetail extends CreateRecord
{
    protected static string $resource = ProductDetailResource::class;

    private array $discountData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->discountData = [
            'has_discount' => (bool) ($data['has_discount'] ?? false),
            'quantity' => $data['discount_quantity'] ?? null,
            'percentage' => $data['discount_percentage'] ?? null,
        ];

        unset($data['has_discount'], $data['discount_quantity'], $data['discount_percentage']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncDiscount($this->record);
    }

    private function syncDiscount(ProductDetail $productDetail): void
    {
        if (!$this->discountData['has_discount']) {
            return;
        }

        $productDetail->discount()->create([
            'quantity' => (int) $this->discountData['quantity'],
            'discount_percentage' => (float) $this->discountData['percentage'],
        ]);
    }
}
