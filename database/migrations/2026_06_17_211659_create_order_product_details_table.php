<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_product_detail', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('product_detail_id')
                ->constrained('product_details')
                ->cascadeOnDelete();

            $table->decimal('discount', 10, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);

            $table->timestamps();

            $table->primary(['order_id', 'product_detail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_product_detail');
    }
};
