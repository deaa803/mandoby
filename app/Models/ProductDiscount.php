<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_detail_id',
        'quantity',
        'discount_percentage',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'discount_percentage' => 'decimal:2',
    ];

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class);
    }

    public function appliesToQuantity(int $quantity): bool
    {
        return $quantity >= $this->quantity;
    }
}
