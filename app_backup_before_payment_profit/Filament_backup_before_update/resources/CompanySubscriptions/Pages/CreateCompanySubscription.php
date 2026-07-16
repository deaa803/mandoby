<?php

namespace App\Filament\Resources\CompanySubscriptions\Pages;

use App\Filament\Resources\CompanySubscriptions\CompanySubscriptionResource;
use App\Models\SubscriptionPlan;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanySubscription extends CreateRecord
{
    protected static string $resource = CompanySubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['end_date']) && !empty($data['subscription_plan_id'])) {
            $plan = SubscriptionPlan::find($data['subscription_plan_id']);

            if ($plan) {
                $startDate = !empty($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : now();
                $data['start_date'] = $startDate;
                $data['end_date'] = $startDate->copy()->addDays($plan->duration_days);
            }
        }

        return $data;
    }
}
