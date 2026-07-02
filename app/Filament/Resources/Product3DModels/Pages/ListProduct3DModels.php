<?php

namespace App\Filament\Resources\Product3DModels\Pages;

use App\Filament\Resources\Product3DModels\Product3DModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProduct3DModels extends ListRecords
{
    protected static string $resource = Product3DModelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
