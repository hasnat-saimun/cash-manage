<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'permissions',
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

    protected $casts = [
        'permissions' => 'array',
    ];

    // role helpers
    public function isSuperAdmin() { return $this->role === 'superAdmin'; }
    public function isGeneralAdmin() { return $this->role === 'general admin'; }
    public function isCashier() { return $this->role === 'cashier'; }

    public function hasPermission(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $perms = $this->permissions ?? [];
        return in_array($key, $perms, true);
    }

    /**
     * Businesses this user belongs to.
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }
}
