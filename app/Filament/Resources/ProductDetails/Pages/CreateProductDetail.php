<?php

namespace App\Filament\Resources\ProductDetails\Pages;

use App\Filament\Resources\ProductDetails\ProductDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductDetail extends CreateRecord
{
    protected static string $resource = ProductDetailResource::class;
}
