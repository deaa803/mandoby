<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'latitude',
        'longitude',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            // حذف الشركة عبر Eloquent أولاً حتى تُحذف حسابات سائقيها قبل السيارات.
            if ($user->user_type === 'company' && $user->company) {
                $user->company->delete();
            }
        });
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function shippingOffice()
    {
        return $this->hasOne(ShippingOffice::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->user_type === 'admin';
    }
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    public function unreadAppNotifications()
    {
        return $this->hasMany(AppNotification::class)->whereNull('read_at');
    }

}
