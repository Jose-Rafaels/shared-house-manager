<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFundEntry extends Model
{
    protected $fillable = [
        'type',
        'member_id',
        'title',
        'amount',
        'entry_date',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
