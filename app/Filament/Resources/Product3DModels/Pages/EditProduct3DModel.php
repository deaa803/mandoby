<?php

namespace App\Filament\Resources\Product3DModels\Pages;

use App\Filament\Resources\Product3DModels\Product3DModelResource;
use App\Models\ProductDetail;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct3DModel extends EditRecord
{
    protected static string $resource = Product3DModelResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $productDetail = ProductDetail::findOrFail($data['product_detail_id']);
        $data['company_id'] = $productDetail->company_id;

        if (($data['status'] ?? null) === 'completed') {
            $data['progress'] = 100;
            $data['generated_at'] ??= now();
            $data['error_message'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
