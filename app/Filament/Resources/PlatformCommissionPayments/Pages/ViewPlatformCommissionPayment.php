<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Pages;

use App\Filament\Resources\PlatformCommissionPayments\PlatformCommissionPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPlatformCommissionPayment extends ViewRecord
{
    protected static string $resource = PlatformCommissionPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('تعديل'),
            DeleteAction::make()
                ->label('حذف')
                ->requiresConfirmation(),
        ];
    }
}
