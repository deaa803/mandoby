<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Pages;

use App\Filament\Resources\PlatformCommissionPayments\PlatformCommissionPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPlatformCommissionPayment extends EditRecord
{
    protected static string $resource = PlatformCommissionPaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? 'pending') === 'pending') {
            $data['reviewed_by_user_id'] = null;
            $data['reviewed_at'] = null;
        } else {
            $data['reviewed_by_user_id'] = auth()->id();
            $data['reviewed_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض'),
            DeleteAction::make()
                ->label('حذف')
                ->requiresConfirmation(),
        ];
    }
}
