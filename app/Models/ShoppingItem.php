<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingItem extends Model
{
    protected $fillable = [
        'name',
        'priority',
        'notes',
        'added_by_member_id',
        'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'added_by_member_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ShoppingPurchase::class);
    }
}
