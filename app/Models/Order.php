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
        'eta_warning_sent_at',
        'eta_late_sent_at',
        'delivery_distance_km',
        'extra_delivery_km',
        'extra_delivery_fee',
        'delivery_qr_code',
        'delivery_qr_used_at',
        'delivered_at',
        'paid_amount',
        'remaining_amount',
        'driver_id'
    ];

    protected $casts = [
        'date' => 'date',
        'estimated_delivery_at' => 'datetime',
        'eta_last_calculated_at' => 'datetime',
        'eta_warning_sent_at' => 'datetime',
        'eta_late_sent_at' => 'datetime',
        'delivery_distance_km' => 'decimal:2',
        'extra_delivery_km' => 'integer',
        'extra_delivery_fee' => 'decimal:2',
        'delivery_qr_used_at' => 'datetime',
        'delivered_at' => 'datetime',
        'total_price' => 'decimal:2',
        'commission' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
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
