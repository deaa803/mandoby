<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PlatformCommissionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'amount',
        'paid_at',
        'payment_method',
        'reference_number',
        'proof_path',
        'notes',
        'status',
        'submission_source',
        'submitted_by_user_id',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'proof_url',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path
            ? Storage::disk('public')->url($this->proof_path)
            : null;
    }
}
