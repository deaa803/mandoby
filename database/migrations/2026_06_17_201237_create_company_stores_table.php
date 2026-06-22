<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_store', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->integer('return_days')->default(0);

            $table->timestamps();

            $table->primary(['company_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_store');
    }
};
