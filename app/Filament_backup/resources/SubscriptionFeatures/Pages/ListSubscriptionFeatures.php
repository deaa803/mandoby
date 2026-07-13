<?php

namespace App\Filament\Resources\SubscriptionFeatures\Pages;

use App\Filament\Resources\SubscriptionFeatures\SubscriptionFeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionFeatures extends ListRecords
{
    protected static string $resource = SubscriptionFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
