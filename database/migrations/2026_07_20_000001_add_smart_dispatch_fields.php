<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table): void {
            $table->decimal('package_weight_kg', 10, 3)
                ->nullable()
                ->after('min_order_quantity');
        });

        Schema::table('company_cars', function (Blueprint $table): void {
            $table->decimal('max_load_kg', 10, 2)
                ->nullable()
                ->after('plate_number');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('total_weight_kg', 12, 3)
                ->default(0)
                ->after('total_price');

            $table->decimal('required_load_kg', 12, 3)
                ->default(0)
                ->after('total_weight_kg');

            $table->string('driver_assignment_method', 20)
                ->nullable()
                ->after('driver_id');

            $table->timestamp('driver_assigned_at')
                ->nullable()
                ->after('driver_assignment_method');
        });

        Schema::table('order_product_detail', function (Blueprint $table): void {
            $table->decimal('package_weight_kg', 10, 3)
                ->default(0)
                ->after('quantity');

            $table->decimal('line_weight_kg', 12, 3)
                ->default(0)
                ->after('package_weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('order_product_detail', function (Blueprint $table): void {
            $table->dropColumn(['package_weight_kg', 'line_weight_kg']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'total_weight_kg',
                'required_load_kg',
                'driver_assignment_method',
                'driver_assigned_at',
            ]);
        });

        Schema::table('company_cars', function (Blueprint $table): void {
            $table->dropColumn('max_load_kg');
        });

        Schema::table('product_details', function (Blueprint $table): void {
            $table->dropColumn('package_weight_kg');
        });
    }
};
