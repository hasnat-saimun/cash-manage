<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Business extends Model
{
    protected $fillable = ['name','slug','settings'];

    protected $casts = ['settings' => 'array'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }
}
