<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRING = 'expiring';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $appends = [
        'days_remaining',
    ];

    protected $fillable = [
        'company_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'last_expiring_7_notified_at',
        'last_expiring_3_notified_at',
        'expired_notified_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_expiring_7_notified_at' => 'datetime',
        'last_expiring_3_notified_at' => 'datetime',
        'expired_notified_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function scopeUsable($query)
    {
        return $query
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_EXPIRING])
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }
}
