<?php

namespace App\Filament\Resources\ProductDetails\Pages;

use App\Filament\Resources\ProductDetails\ProductDetailResource;
use App\Models\ProductDetail;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProductDetail extends EditRecord
{
    protected static string $resource = ProductDetailResource::class;

    private array $discountData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('discount');

        $data['has_discount'] = $this->record->discount !== null;
        $data['discount_quantity'] = $this->record->discount?->quantity;
        $data['discount_percentage'] = $this->record->discount?->discount_percentage;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->discountData = [
            'has_discount' => (bool) ($data['has_discount'] ?? false),
            'quantity' => $data['discount_quantity'] ?? null,
            'percentage' => $data['discount_percentage'] ?? null,
        ];

        unset($data['has_discount'], $data['discount_quantity'], $data['discount_percentage']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncDiscount($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    private function syncDiscount(ProductDetail $productDetail): void
    {
        if (!$this->discountData['has_discount']) {
            $productDetail->discount()?->delete();

            return;
        }

        $productDetail->discount()->updateOrCreate(
            ['product_detail_id' => $productDetail->id],
            [
                'quantity' => (int) $this->discountData['quantity'],
                'discount_percentage' => (float) $this->discountData['percentage'],
            ]
        );
    }
}
