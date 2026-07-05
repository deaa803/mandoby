<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')
                ->constrained('features')
                ->onDelete('cascade');
            $table->foreignId('product_detail_id')
                ->constrained('product_details')
                ->onDelete('cascade');
            $table->string('value');
            $table->unique(['feature_id', 'product_detail_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_product_details');
    }
};
