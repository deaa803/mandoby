<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Services\PlatformProfitService;
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
        $commissionRate = app(PlatformProfitService::class)->rate();

        /*
         * كل طلب في النظام يعود إلى شركة واحدة من خلال منتجات الطلب.
         * نستخدم distinct حتى لا تتكرر الدفعة إذا احتوى الطلب على عدة منتجات.
         */
        $orderCompanyPairs = DB::table('order_product_detail as opd')
            ->join('product_details as pd', 'pd.id', '=', 'opd.product_detail_id')
            ->select([
                'pd.company_id',
                'opd.order_id',
            ])
            ->distinct();

        /*
         * الربح الفعلي للمنصة = مجموع دفعات طلبات الشركة × نسبة العمولة.
         */
        $profitTotals = DB::table('payments as p')
            ->joinSub(
                $orderCompanyPairs,
                'order_companies',
                'order_companies.order_id',
                '=',
                'p.order_id'
            )
            ->join('orders as o', 'o.id', '=', 'p.order_id')
            ->where('o.status', '!=', 'cancelled')
            ->select('order_companies.company_id')
            ->selectRaw('COUNT(DISTINCT p.order_id) as orders_count')
            ->selectRaw('COALESCE(SUM(p.amount), 0) as paid_total')
            ->selectRaw(
                'ROUND(COALESCE(SUM(p.amount), 0) * ?, 2) as platform_profit',
                [$commissionRate]
            )
            ->groupBy('order_companies.company_id');

        $companies = Company::query()
            ->leftJoinSub(
                $profitTotals,
                'company_profit_totals',
                'company_profit_totals.company_id',
                '=',
                'companies.id'
            )
            ->select([
                'companies.id',
                'companies.name_company',
                'companies.logo',
            ])
            ->selectRaw('COALESCE(company_profit_totals.orders_count, 0) as orders_count')
            ->selectRaw('COALESCE(company_profit_totals.paid_total, 0) as paid_total')
            ->selectRaw('COALESCE(company_profit_totals.platform_profit, 0) as platform_profit')
            ->orderByDesc('platform_profit')
            ->orderBy('companies.name_company')
            ->limit(8)
            ->get()
            ->map(function ($company) {
                $company->admin_url = CompanyResource::getUrl('view', [
                    'record' => $company->id,
                ]);

                return $company;
            });

        return [
            'companies' => $companies,
            'maxProfit' => max(1, (float) $companies->max('platform_profit')),
            'currency' => config('app.currency', 'SYP'),
            'commissionPercentage' => $commissionRate * 100,
            'isArabic' => true,
        ];
    }
}
