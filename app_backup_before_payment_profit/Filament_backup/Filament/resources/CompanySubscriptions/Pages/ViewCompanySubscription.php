<?php

namespace App\Filament\Resources\CompanySubscriptions\Pages;

use App\Filament\Resources\CompanySubscriptions\CompanySubscriptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCompanySubscription extends ViewRecord
{
    protected static string $resource = CompanySubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
