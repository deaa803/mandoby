<?php

namespace App\Filament\Resources\SubscriptionFeatures\Pages;

use App\Filament\Resources\SubscriptionFeatures\SubscriptionFeatureResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriptionFeature extends ViewRecord
{
    protected static string $resource = SubscriptionFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
