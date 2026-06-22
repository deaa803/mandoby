<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_type',
        'driver_name',
        'plate_number',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function drivers()
    {
        return $this->hasMany(Driver::class,);
    }
}
