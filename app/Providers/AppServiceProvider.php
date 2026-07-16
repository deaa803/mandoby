<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\PlatformCommissionPayment;
use App\Observers\PaymentObserver;
use App\Observers\PlatformCommissionPaymentObserver;
use App\Services\PlatformProfitService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlatformProfitService::class);
    }

    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        PlatformCommissionPayment::observe(PlatformCommissionPaymentObserver::class);
    }
}
