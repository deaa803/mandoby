<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('duration_days')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subscription_plan_features', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();
            $table->foreignId('subscription_feature_id')
                ->constrained('subscription_features')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['subscription_plan_id', 'subscription_feature_id']);
        });

        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'expiring', 'expired', 'cancelled'])
                ->default('active')
                ->index();
            $table->timestamp('last_expiring_7_notified_at')->nullable();
            $table->timestamp('last_expiring_3_notified_at')->nullable();
            $table->timestamp('expired_notified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
        Schema::dropIfExists('subscription_plan_features');
        Schema::dropIfExists('subscription_features');
        Schema::dropIfExists('subscription_plans');
    }
};
