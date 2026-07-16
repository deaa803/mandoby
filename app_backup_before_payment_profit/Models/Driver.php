<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'company_car_id',
        'status',
        'current_lat',
        'current_lng',
        'last_location_at',
        'fcm_token',
    ];

    protected $casts = [
        'last_location_at' => 'datetime',
        'current_lat' => 'decimal:7',
        'current_lng' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(CompanyCar::class, 'company_car_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
