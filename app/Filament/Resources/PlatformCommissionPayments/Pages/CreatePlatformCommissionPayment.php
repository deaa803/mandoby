<?php

namespace App\Filament\Resources\PlatformCommissionPayments\Pages;

use App\Filament\Resources\PlatformCommissionPayments\PlatformCommissionPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlatformCommissionPayment extends CreateRecord
{
    protected static string $resource = PlatformCommissionPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submission_source'] = 'admin';
        $data['submitted_by_user_id'] = auth()->id();

        if (($data['status'] ?? 'confirmed') !== 'pending') {
            $data['reviewed_by_user_id'] = auth()->id();
            $data['reviewed_at'] = now();
        }

        return $data;
    }
}
