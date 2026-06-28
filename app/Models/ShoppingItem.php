<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    protected $fillable = [
        'name',
        'priority',
        'notes',
        'added_by_member_id',
        'is_purchased',
    ];

    protected $casts = [
        'is_purchased' => 'boolean',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'added_by_member_id');
    }
}
