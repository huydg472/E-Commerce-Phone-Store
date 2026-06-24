<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'username',
        'password',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roleNames = is_array($roles) ? $roles : [$roles];

        return in_array($this->role?->name, $roleNames, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isAdminOrStaff(): bool
    {
        return $this->hasRole(['admin', 'staff']);
    }

    public function hasPermission(string|array $permissions): bool
    {
        $permissionNames = is_array($permissions) ? $permissions : [$permissions];

        if ($this->isAdmin()) {
            return true;
        }

        return collect($this->role?->permissions ?? [])
            ->pluck('name')
            ->contains(fn($name) => in_array($name, $permissionNames, true));
    }

    public function sendEmailVerificationNotification()
    {
        if ($this->hasVerifiedEmail()) {
            return false;
        }

        if ($this->hasRecentEmailVerificationNotification()) {
            return false;
        }

        $this->notify(new VerifyEmail);

        Cache::put(
            $this->emailVerificationThrottleCacheKey(),
            true,
            now()->addMinutes((int)config('auth.verification.expire', 5))
        );

        return true;
    }

    public function hasRecentEmailVerificationNotification(): bool
    {
        return Cache::has($this->emailVerificationThrottleCacheKey());
    }

    protected function emailVerificationThrottleCacheKey(): string
    {
        return sprintf(
            'email-verification-notification:%s:%s',
            $this->getKey(),
            Str::lower((string)$this->email)
        );
    }
}
