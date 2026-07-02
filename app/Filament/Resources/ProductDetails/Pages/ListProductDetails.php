<?php

namespace App\Filament\Resources\ProductDetails\Pages;

use App\Filament\Resources\ProductDetails\ProductDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductDetails extends ListRecords
{
    protected static string $resource = ProductDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
