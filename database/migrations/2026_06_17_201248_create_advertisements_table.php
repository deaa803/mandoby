<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('product_detail_id')
                ->nullable()
                ->constrained('product_details')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image');

            $table->decimal('price', 10, 2)->default(0);

            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])
                ->default('active');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
