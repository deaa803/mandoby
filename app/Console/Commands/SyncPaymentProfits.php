<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PlatformProfitService;
use Illuminate\Console\Command;

class SyncPaymentProfits extends Command
{
    protected $signature = 'payments:sync-profits';

    protected $description = 'إعادة حساب المدفوع والمتبقي وعمولة المنصة لجميع الطلبات اعتمادًا على الدفعات الفعلية';

    public function handle(PlatformProfitService $platformProfitService): int
    {
        $ordersCount = Order::query()->count();

        if ($ordersCount === 0) {
            $this->info('لا توجد طلبات لإعادة حسابها.');

            return self::SUCCESS;
        }

        $this->info('يتم الآن إعادة حساب قيم الطلبات من الدفعات الفعلية...');
        $bar = $this->output->createProgressBar($ordersCount);
        $bar->start();

        Order::query()
            ->orderBy('id')
            ->chunkById(200, function ($orders) use ($platformProfitService, $bar): void {
                foreach ($orders as $order) {
                    $platformProfitService->syncOrder($order);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info('تمت إعادة حساب جميع الطلبات بنجاح.');
        $this->line('نسبة عمولة المنصة الحالية: ' . ($platformProfitService->rate() * 100) . '٪ من الدفعات.');

        return self::SUCCESS;
    }
}
