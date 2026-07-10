<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'has_3d_access')) {
                $table->dropColumn('has_3d_access');
            }

            if (Schema::hasColumn('companies', 'model_3d_expires_at')) {
                $table->dropColumn('model_3d_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'has_3d_access')) {
                $table->boolean('has_3d_access')->default(false);
            }

            if (!Schema::hasColumn('companies', 'model_3d_expires_at')) {
                $table->timestamp('model_3d_expires_at')->nullable();
            }
        });
    }
};
