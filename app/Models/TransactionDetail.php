<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $table = 'transaction_details';
    
    protected $fillable = [
        'business_id',
        'name',
        'type',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get active transaction details for a business
     */
    public static function activeForBusiness($businessId, $type = null)
    {
        $query = self::where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('name', 'asc');
        
        if ($type) {
            $query->where(function ($q) use ($type) {
                $q->whereNull('type')
                  ->orWhere('type', $type);
            });
        }
        
        return $query->get();
    }
}
