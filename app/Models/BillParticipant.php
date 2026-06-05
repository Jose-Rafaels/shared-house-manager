<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'housemate_id',
        'share_amount',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function housemate(): BelongsTo
    {
        return $this->belongsTo(Housemate::class);
    }
}
