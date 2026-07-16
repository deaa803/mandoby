<?php

namespace App\Filament\Resources\Product3DModels\Pages;

use App\Filament\Resources\Product3DModels\Product3DModelResource;
use App\Models\ProductDetail;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct3DModel extends CreateRecord
{
    protected static string $resource = Product3DModelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $productDetail = ProductDetail::findOrFail($data['product_detail_id']);
        $data['company_id'] = $productDetail->company_id;

        if (($data['status'] ?? null) === 'completed' && empty($data['generated_at'])) {
            $data['generated_at'] = now();
        }

        return $data;
    }
}
