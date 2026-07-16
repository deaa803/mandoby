<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Services\PlatformProfitService;
use Filament\Widgets\Widget;

class CompanyProfitOverview extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.company-profit-overview';

    protected function getViewData(): array
    {
        $service = app(PlatformProfitService::class);

        $companies = Company::query()
            ->select(['id', 'name_company', 'logo'])
            ->get()
            ->map(function (Company $company) use ($service): Company {
                $statement = $service->companyStatement($company, true);

                $company->setAttribute('paid_total', $statement['paid_orders_total']);
                $company->setAttribute('platform_profit', $statement['accrued_commission']);
                $company->setAttribute('platform_collected', $statement['confirmed_platform_payments']);
                $company->setAttribute('platform_pending', $statement['pending_platform_payments']);
                $company->setAttribute('platform_remaining', $statement['remaining_amount']);
                $company->setAttribute('platform_status', $statement['status']);
                $company->setAttribute('platform_status_label', $statement['status_label']);
                $company->setAttribute('admin_url', CompanyResource::getUrl('view', [
                    'record' => $company->id,
                ]));

                return $company;
            })
            ->sortByDesc('platform_remaining')
            ->take(8)
            ->values();

        return [
            'companies' => $companies,
            'currency' => config('app.currency', 'SYP'),
            'commissionPercentage' => $service->rate() * 100,
            'isArabic' => true,
        ];
    }
}
