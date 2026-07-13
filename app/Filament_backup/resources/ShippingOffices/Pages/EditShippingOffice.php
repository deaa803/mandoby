<?php

namespace App\Filament\Resources\ShippingOffices\Pages;

use App\Filament\Resources\ShippingOffices\ShippingOfficeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingOffice extends EditRecord
{
    protected static string $resource = ShippingOfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
