<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedExpenseParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'shared_expense_id',
        'housemate_id',
        'share_amount',
    ];

    public function sharedExpense(): BelongsTo
    {
        return $this->belongsTo(SharedExpense::class);
    }

    public function housemate(): BelongsTo
    {
        return $this->belongsTo(Housemate::class);
    }
}
