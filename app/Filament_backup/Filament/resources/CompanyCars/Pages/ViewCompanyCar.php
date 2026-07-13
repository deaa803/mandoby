<?php

namespace App\Filament\Resources\CompanyCars\Pages;

use App\Filament\Resources\CompanyCars\CompanyCarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCompanyCar extends ViewRecord
{
    protected static string $resource = CompanyCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
