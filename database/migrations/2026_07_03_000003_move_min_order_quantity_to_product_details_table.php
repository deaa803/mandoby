<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_details', 'min_order_quantity')) {
            Schema::table('product_details', function (Blueprint $table) {
                $table->unsignedInteger('min_order_quantity')
                    ->default(1)
                    ->after('price');
            });
        }

        if (Schema::hasColumn('products', 'min_order_quantity')) {
            DB::table('product_details')
                ->join('products', 'products.id', '=', 'product_details.product_id')
                ->select([
                    'product_details.id',
                    'products.min_order_quantity',
                ])
                ->orderBy('product_details.id')
                ->get()
                ->each(function ($row) {
                    DB::table('product_details')
                        ->where('id', $row->id)
                        ->update([
                            'min_order_quantity' => max((int) $row->min_order_quantity, 1),
                        ]);
                });

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('min_order_quantity');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'min_order_quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('min_order_quantity')
                    ->default(1)
                    ->after('description');
            });
        }

        DB::table('products')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($product) {
                $minimum = DB::table('product_details')
                    ->where('product_id', $product->id)
                    ->min('min_order_quantity');

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'min_order_quantity' => max((int) ($minimum ?? 1), 1),
                    ]);
            });

        if (Schema::hasColumn('product_details', 'min_order_quantity')) {
            Schema::table('product_details', function (Blueprint $table) {
                $table->dropColumn('min_order_quantity');
            });
        }
    }
};
