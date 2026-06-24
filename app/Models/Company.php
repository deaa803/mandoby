<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name_company',
        'description',
        'logo',
    ];

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
        return $this->hasMany(Driver::class, 'company_id');
    }
    public function advertisements()
    {
        return $this->hasMany(Advertisement::class, 'company_id');
    }
}
