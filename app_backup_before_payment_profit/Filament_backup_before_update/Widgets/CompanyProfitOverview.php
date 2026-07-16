<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

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
        $orderCompanyPairs = DB::table('orders as o')
            ->join('order_product_detail as opd', 'opd.order_id', '=', 'o.id')
            ->join('product_details as pd', 'pd.id', '=', 'opd.product_detail_id')
            ->where('o.status', '!=', 'cancelled')
            ->select(['pd.company_id', 'o.id as order_id', 'o.commission'])
            ->distinct();

        $profitTotals = DB::query()
            ->fromSub($orderCompanyPairs, 'company_orders')
            ->select('company_id')
            ->selectRaw('COUNT(order_id) as orders_count')
            ->selectRaw('COALESCE(SUM(commission), 0) as platform_profit')
            ->groupBy('company_id');

        $companies = Company::query()
            ->leftJoinSub(
                $profitTotals,
                'company_profit_totals',
                'company_profit_totals.company_id',
                '=',
                'companies.id'
            )
            ->select(['companies.id', 'companies.name_company', 'companies.logo'])
            ->selectRaw('COALESCE(company_profit_totals.orders_count, 0) as orders_count')
            ->selectRaw('COALESCE(company_profit_totals.platform_profit, 0) as platform_profit')
            ->orderByDesc('platform_profit')
            ->orderBy('companies.name_company')
            ->limit(8)
            ->get()
            ->map(function ($company) {
                $company->admin_url = CompanyResource::getUrl('view', ['record' => $company->id]);
                return $company;
            });

        $maxProfit = max(1, (float) $companies->max('platform_profit'));

        return [
            'companies' => $companies,
            'maxProfit' => $maxProfit,
            'currency' => config('app.currency', 'SYP'),
            'isArabic' => true,
        ];
    }
}
