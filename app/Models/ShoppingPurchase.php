<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingPurchase extends Model
{
    protected $fillable = [
        'shopping_item_id',
        'purchased_by_member_id',
        'amount',
        'purchased_on',
        'notes',
    ];

    protected $casts = [
        'purchased_on' => 'date',
    ];

    public function shoppingItem(): BelongsTo
    {
        return $this->belongsTo(ShoppingItem::class);
    }

    public function purchasedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'purchased_by_member_id');
    }
}
