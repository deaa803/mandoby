<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_qr_code', 64)
                ->nullable()
                ->unique()
                ->after('eta_last_calculated_at');

            $table->timestamp('delivery_qr_used_at')
                ->nullable()
                ->after('delivery_qr_code');

            $table->timestamp('delivered_at')
                ->nullable()
                ->after('delivery_qr_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_qr_code',
                'delivery_qr_used_at',
                'delivered_at',
            ]);
        });
    }
};
