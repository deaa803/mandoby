<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\SmartDispatchService;
use Illuminate\Console\Command;

class SyncOrderWeights extends Command
{
    protected $signature = 'dispatch:sync-weights {--order=}';

    protected $description = 'Recalculate order weights and package-weight snapshots for smart dispatch.';

    public function handle(SmartDispatchService $service): int
    {
        $query = Order::query()->with('productDetails.product');

        if ($this->option('order')) {
            $query->whereKey((int) $this->option('order'));
        }

        $updated = 0;
        $skipped = 0;

        $query->orderBy('id')->chunkById(100, function ($orders) use ($service, &$updated, &$skipped): void {
            foreach ($orders as $order) {
                $result = $service->calculateOrderWeight($order);

                if (! $result['ready']) {
                    $skipped++;
                    $names = collect($result['missing_products'])
                        ->pluck('product_name')
                        ->implode(', ');
                    $this->warn("Order #{$order->id} skipped. Missing weight: {$names}");
                    continue;
                }

                $updated++;
                $this->line(
                    "Order #{$order->id}: {$result['total_weight_kg']} kg "
                    . "({$result['required_load_kg']} kg with safety margin)",
                );
            }
        });

        $this->info("Updated orders: {$updated}; skipped orders: {$skipped}.");

        return self::SUCCESS;
    }
}
