<?php

namespace App\Filament\Resources\CompanyCars\Pages;

use App\Filament\Resources\CompanyCars\CompanyCarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyCars extends ListRecords
{
    protected static string $resource = CompanyCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
