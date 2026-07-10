<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function plans()
    {
        return $this->belongsToMany(
            SubscriptionPlan::class,
            'subscription_plan_features',
            'subscription_feature_id',
            'subscription_plan_id'
        )->withTimestamps();
    }
}
