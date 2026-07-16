<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name_company',
        'description',
        'logo',
        'delivery_radius_km',
        'extra_delivery_fee_per_km',
    ];

    protected $casts = [
        'delivery_radius_km' => 'decimal:2',
        'extra_delivery_fee_per_km' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Company $company): void {
            // منع تعارض الحذف: احذف حسابات السائقين قبل حذف سيارات الشركة.
            $company->loadMissing('cars.driver.user');

            foreach ($company->cars as $car) {
                $car->driver?->user?->delete();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cars()
    {
        return $this->hasMany(CompanyCar::class);
    }

    public function productDetails()
    {
        return $this->hasMany(ProductDetail::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'company_store')
            ->withPivot('return_days')
            ->withTimestamps();
    }

    public function drivers()
    {
        return $this->hasManyThrough(
            Driver::class,
            CompanyCar::class,
            'company_id',
            'company_car_id',
            'id',
            'id'
        );
    }

    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'company_id');
    }

    public function product3dModels()
    {
        return $this->hasMany(Product3DModel::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function platformCommissionPayments()
    {
        return $this->hasMany(PlatformCommissionPayment::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->usable()
            ->latestOfMany('end_date');
    }

    public function hasActiveFeature(string $featureKey): bool
    {
        return $this->subscriptions()
            ->usable()
            ->whereHas('plan.features', function ($query) use ($featureKey) {
                $query->where('key', $featureKey)
                    ->where('is_active', true);
            })
            ->exists();
    }

    public function activeFeatureKeys(): array
    {
        return $this->subscriptions()
            ->usable()
            ->with('plan.features')
            ->get()
            ->flatMap(fn (CompanySubscription $subscription) => $subscription->plan?->features ?? [])
            ->where('is_active', true)
            ->pluck('key')
            ->unique()
            ->values()
            ->all();
    }
}
