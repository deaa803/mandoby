<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            $table->decimal('total_price', 15, 2)->default(0);
            $table->date('date');
            $table->decimal('commission', 15, 2)->default(0);

            $table->enum('status', [
                'pending',
                'preparing',
                'delivering',
                'delivered',
                'cancelled',
            ])->default('pending');

            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('eta_last_calculated_at')->nullable();

            $table->decimal('delivery_distance_km', 8, 2)->default(0);
            $table->unsignedInteger('extra_delivery_km')->default(0);
            $table->decimal('extra_delivery_fee', 15, 2)->default(0);

            $table->string('delivery_qr_code', 64)->nullable()->unique();
            $table->timestamp('delivery_qr_used_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['status', 'estimated_delivery_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
