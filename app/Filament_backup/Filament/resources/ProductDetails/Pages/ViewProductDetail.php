<?php

namespace App\Filament\Resources\ProductDetails\Pages;

use App\Filament\Resources\ProductDetails\ProductDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductDetail extends ViewRecord
{
    protected static string $resource = ProductDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
