<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFundEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'housemate_id',
        'title',
        'amount',
        'entry_date',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function housemate(): BelongsTo
    {
        return $this->belongsTo(Housemate::class);
    }
}
