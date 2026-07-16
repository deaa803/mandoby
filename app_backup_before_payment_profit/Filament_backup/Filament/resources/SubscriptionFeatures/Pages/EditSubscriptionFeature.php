<?php

namespace App\Filament\Resources\SubscriptionFeatures\Pages;

use App\Filament\Resources\SubscriptionFeatures\SubscriptionFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionFeature extends EditRecord
{
    protected static string $resource = SubscriptionFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
