<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            if (!Schema::hasColumn('product_details', 'price')) {
                $table->decimal('price', 15, 2)->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            if (Schema::hasColumn('product_details', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
