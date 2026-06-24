<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'company_id',
        'category_id',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }



    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product_detail')
            ->withPivot('discount','price','quantity')
            ->withTimestamps();
    }
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_product_details')
            ->withPivot('value')
            ->withTimestamps();
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function advertisement()
    {
        return $this->hasMany(Advertisement::class);
    }

}
