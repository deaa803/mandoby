<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE device_tokens MODIFY app_type ENUM('driver','company','store','customer') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE device_tokens MODIFY app_type ENUM('driver','company','customer') NOT NULL");
    }
};
