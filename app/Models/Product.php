<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_order_quantity',
    ];

    public function details()
    {
        return $this->hasMany(ProductDetail::class);
    }

}
