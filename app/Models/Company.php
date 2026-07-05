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
        'has_3d_access',
        'model_3d_expires_at',
    ];

    protected $casts = [
        'has_3d_access' => 'boolean',
        'model_3d_expires_at' => 'datetime',
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
}
