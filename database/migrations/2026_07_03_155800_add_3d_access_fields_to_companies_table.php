<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('has_3d_access')
                ->default(false)
                ->after('logo');

            $table->timestamp('model_3d_expires_at')
                ->nullable()
                ->after('has_3d_access');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'has_3d_access',
                'model_3d_expires_at',
            ]);
        });
    }
};
