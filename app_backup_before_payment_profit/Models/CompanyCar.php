<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_type',
        'plate_number',
    ];

    protected static function booted(): void
    {
        static::deleting(function (CompanyCar $car): void {
            // احذف حساب السائق أولاً حتى لا يمنع المفتاح الأجنبي حذف السيارة.
            $driverUser = $car->driver?->user;

            if ($driverUser) {
                $driverUser->delete();
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class, 'company_car_id');
    }
}
