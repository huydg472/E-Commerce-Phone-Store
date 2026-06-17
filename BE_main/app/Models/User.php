<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
            ->contains(fn ($name) => in_array($name, $permissionNames, true));
    }
}
