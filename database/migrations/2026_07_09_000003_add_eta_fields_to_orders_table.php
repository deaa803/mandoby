<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('estimated_delivery_minutes')->nullable()->after('status');
            $table->timestamp('estimated_delivery_at')->nullable()->after('estimated_delivery_minutes');
            $table->timestamp('eta_last_calculated_at')->nullable()->after('estimated_delivery_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'estimated_delivery_minutes',
                'estimated_delivery_at',
                'eta_last_calculated_at',
            ]);
        });
    }
};
