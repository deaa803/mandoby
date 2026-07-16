<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];


    public function productDetails()
    {
       return $this->belongsToMany(ProductDetail::class, 'feature_product_details')
            ->withPivot('value')
            ->withTimestamps();
    }
}
