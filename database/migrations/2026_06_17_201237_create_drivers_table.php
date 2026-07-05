<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /*
             * لا يوجد company_id هنا عمداً.
             * شركة السائق تُعرف من السيارة:
             * drivers.company_car_id -> company_cars.company_id
             */
            $table->foreignId('company_car_id')
                ->nullable()
                ->unique()
                ->constrained('company_cars')
                ->nullOnDelete();

            $table->string('fcm_token', 512)
                ->nullable()
                ->unique();

            $table->enum('status', ['available', 'busy', 'offline'])
                ->default('offline');

            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->timestamp('last_location_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
