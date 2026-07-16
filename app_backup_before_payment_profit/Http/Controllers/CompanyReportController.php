<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyReportController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()?->company;

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company account not found',
                'data' => null,
            ], 404);
        }

        if (!$company->hasActiveFeature('advanced_reports')) {
            return response()->json([
                'status' => false,
                'message' => 'Advanced reports require an active subscription',
                'data' => null,
            ], 403);
        }

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->subDays(30)->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        return response()->json([
            'status' => true,
            'message' => 'Company reports retrieved successfully',
            'data' => [
                'period' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
                'sales_summary' => $this->salesSummary($company->id, $dateFrom, $dateTo),
                'daily_sales' => $this->dailySales($company->id, $dateFrom, $dateTo),
                'monthly_sales' => $this->monthlySales($company->id, $dateFrom, $dateTo),
                'top_selling_products' => $this->topSellingProducts($company->id, $dateFrom, $dateTo),
                'low_selling_products' => $this->lowSellingProducts($company->id, $dateFrom, $dateTo),
                'profit_report' => $this->profitReport($company->id, $dateFrom, $dateTo),
                'discount_impact' => $this->discountImpact($company->id, $dateFrom, $dateTo),
                'inventory_movement' => $this->inventoryMovement($company->id, $dateFrom, $dateTo),
                'top_customers' => $this->topCustomers($company->id, $dateFrom, $dateTo),
            ],
        ]);
    }

    private function baseOrders(int $companyId, string $dateFrom, string $dateTo)
    {
        return Order::query()
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->whereHas('productDetails', function ($query) use ($companyId) {
                $query->where('product_details.company_id', $companyId);
            });
    }

    private function companyOrderLines(int $companyId, string $dateFrom, string $dateTo)
    {
        return DB::table('order_product_detail as opd')
            ->join('orders', 'orders.id', '=', 'opd.order_id')
            ->join('product_details as pd', 'pd.id', '=', 'opd.product_detail_id')
            ->join('products', 'products.id', '=', 'pd.product_id')
            ->where('pd.company_id', $companyId)
            ->whereBetween('orders.date', [$dateFrom, $dateTo]);
    }

    private function salesSummary(int $companyId, string $dateFrom, string $dateTo): array
    {
        $orders = $this->baseOrders($companyId, $dateFrom, $dateTo);
        $lines = $this->companyOrderLines($companyId, $dateFrom, $dateTo)
            ->selectRaw('COALESCE(SUM(opd.price * opd.quantity), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(opd.discount), 0) as discounts')
            ->selectRaw('COALESCE(SUM((opd.price * opd.quantity) - opd.discount), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(opd.quantity), 0) as sold_quantity')
            ->first();

        return [
            'orders_count' => (clone $orders)->count(),
            'gross_sales' => round((float) $lines->gross_sales, 2),
            'discounts' => round((float) $lines->discounts, 2),
            'net_sales' => round((float) $lines->net_sales, 2),
            'extra_delivery_fees' => round((float) (clone $orders)->sum('extra_delivery_fee'), 2),
            'sold_quantity' => (int) $lines->sold_quantity,
        ];
    }

    private function dailySales(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->companyOrderLines($companyId, $dateFrom, $dateTo)
            ->selectRaw('orders.date as date')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('SUM(opd.quantity) as sold_quantity')
            ->selectRaw('SUM((opd.price * opd.quantity) - opd.discount) as net_sales')
            ->groupBy('orders.date')
            ->orderBy('orders.date')
            ->get();
    }

    private function monthlySales(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->companyOrderLines($companyId, $dateFrom, $dateTo)
            ->selectRaw("DATE_FORMAT(orders.date, '%Y-%m') as month")
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('SUM(opd.quantity) as sold_quantity')
            ->selectRaw('SUM((opd.price * opd.quantity) - opd.discount) as net_sales')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function topSellingProducts(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->productSalesQuery($companyId, $dateFrom, $dateTo)
            ->orderByDesc('sold_quantity')
            ->limit(10)
            ->get();
    }

    private function lowSellingProducts(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->productSalesQuery($companyId, $dateFrom, $dateTo)
            ->orderBy('sold_quantity')
            ->limit(10)
            ->get();
    }

    private function productSalesQuery(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->companyOrderLines($companyId, $dateFrom, $dateTo)
            ->selectRaw('pd.id as product_detail_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(opd.quantity) as sold_quantity')
            ->selectRaw('SUM((opd.price * opd.quantity) - opd.discount) as net_sales')
            ->groupBy('pd.id', 'products.name');
    }

    private function profitReport(int $companyId, string $dateFrom, string $dateTo): array
    {
        $summary = $this->salesSummary($companyId, $dateFrom, $dateTo);

        return [
            'gross_sales' => $summary['gross_sales'],
            'discounts' => $summary['discounts'],
            'net_sales' => $summary['net_sales'],
            'note' => 'صافي الربح الحقيقي يحتاج حقل تكلفة المنتج. حالياً التقرير يعرض صافي المبيعات بعد الخصومات.',
        ];
    }

    private function discountImpact(int $companyId, string $dateFrom, string $dateTo): array
    {
        $rows = $this->companyOrderLines($companyId, $dateFrom, $dateTo)
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('COUNT(*) as discounted_lines_count')
            ->selectRaw('SUM(opd.quantity) as discounted_quantity')
            ->selectRaw('SUM(opd.discount) as total_discount')
            ->where('opd.discount', '>', 0)
            ->first();

        return [
            'orders_count' => (int) ($rows->orders_count ?? 0),
            'discounted_lines_count' => (int) ($rows->discounted_lines_count ?? 0),
            'discounted_quantity' => (int) ($rows->discounted_quantity ?? 0),
            'total_discount' => round((float) ($rows->total_discount ?? 0), 2),
        ];
    }

    private function inventoryMovement(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->productSalesQuery($companyId, $dateFrom, $dateTo)
            ->orderByDesc('sold_quantity')
            ->get()
            ->map(fn ($row) => [
                'product_detail_id' => $row->product_detail_id,
                'product_name' => $row->product_name,
                'out_quantity' => (int) $row->sold_quantity,
                'net_sales' => round((float) $row->net_sales, 2),
            ]);
    }

    private function topCustomers(int $companyId, string $dateFrom, string $dateTo)
    {
        return $this->companyOrderLines($companyId, $dateFrom, $dateTo)
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->selectRaw('stores.id as store_id')
            ->selectRaw('stores.name_store as store_name')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('SUM(opd.quantity) as quantity')
            ->selectRaw('SUM((opd.price * opd.quantity) - opd.discount) as net_sales')
            ->groupBy('stores.id', 'stores.name_store')
            ->orderByDesc('net_sales')
            ->limit(10)
            ->get();
    }
}
