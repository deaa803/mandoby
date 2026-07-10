<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_detail_id')
                ->unique()
                ->constrained('product_details')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('discount_percentage', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
    }
};
