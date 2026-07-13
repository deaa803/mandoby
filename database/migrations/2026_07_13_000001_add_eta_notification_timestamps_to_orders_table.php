<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('eta_warning_sent_at')->nullable()->after('eta_last_calculated_at');
            $table->timestamp('eta_late_sent_at')->nullable()->after('eta_warning_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'eta_warning_sent_at',
                'eta_late_sent_at',
            ]);
        });
    }
};
