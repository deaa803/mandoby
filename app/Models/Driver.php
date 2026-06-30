<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'company_car_id',
        'status',
        'current_lat',
        'current_lng',
        'last_location_at',
        'fcm_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function car()
    {
        return $this->belongsTo(CompanyCar::class, 'company_car_id');
    }

}
