<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class clientCreation extends Model
{
    protected $fillable = ['business_id'];

    protected static function booted()
    {
        static::addGlobalScope('business', function ($builder) {
            if (session()->has('business_id')) {
                $builder->where('client_creations.business_id', session('business_id'));
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
