<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Pages;

use App\Filament\Resources\PlatformCommissionPayments\PlatformCommissionPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlatformCommissionPayments extends ListRecords
{
    protected static string $resource = PlatformCommissionPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('تسجيل دفعة جديدة'),
        ];
    }
}
