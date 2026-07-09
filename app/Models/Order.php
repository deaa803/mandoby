<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'total_price',
        'date',
        'commission',
        'status',
        'estimated_delivery_minutes',
        'estimated_delivery_at',
        'eta_last_calculated_at',
        'paid_amount',
        'remaining_amount',
        'driver_id'
    ];

    protected $casts = [
        'date' => 'date',
        'estimated_delivery_at' => 'datetime',
        'eta_last_calculated_at' => 'datetime',
    ];

    public function store(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Store::class);
    }


    public function productDetails(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductDetail::class, 'order_product_detail')
            ->withPivot('discount', 'price', 'quantity')
            ->withTimestamps();
    }
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

}
