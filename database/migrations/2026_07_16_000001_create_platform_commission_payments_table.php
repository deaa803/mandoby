<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_commission_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->dateTime('paid_at');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'rejected',
            ])->default('confirmed');

            $table->enum('submission_source', [
                'admin',
                'company',
            ])->default('admin');

            $table->foreignId('submitted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_commission_payments');
    }
};
