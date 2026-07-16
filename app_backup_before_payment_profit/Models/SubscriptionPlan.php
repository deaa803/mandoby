<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function features()
    {
        return $this->belongsToMany(
            SubscriptionFeature::class,
            'subscription_plan_features',
            'subscription_plan_id',
            'subscription_feature_id'
        )->withTimestamps();
    }

    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }
}
