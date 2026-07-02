<?php

namespace App\Filament\Resources\ProductDetails\Pages;

use App\Filament\Resources\ProductDetails\ProductDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProductDetail extends EditRecord
{
    protected static string $resource = ProductDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
