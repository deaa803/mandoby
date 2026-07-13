<?php

namespace App\Filament\Resources\Product3DModels\Pages;

use App\Filament\Resources\Product3DModels\Product3DModelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct3DModel extends ViewRecord
{
    protected static string $resource = Product3DModelResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
