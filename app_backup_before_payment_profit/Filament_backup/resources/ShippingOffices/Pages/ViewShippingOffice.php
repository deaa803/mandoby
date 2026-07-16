<?php

namespace App\Filament\Resources\ShippingOffices\Pages;

use App\Filament\Resources\ShippingOffices\ShippingOfficeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShippingOffice extends ViewRecord
{
    protected static string $resource = ShippingOfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
