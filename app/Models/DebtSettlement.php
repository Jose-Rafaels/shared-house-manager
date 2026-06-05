<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'shared_expense_id',
        'debtor_housemate_id',
        'creditor_housemate_id',
        'amount',
        'settled_on',
        'notes',
    ];

    protected $casts = [
        'settled_on' => 'date',
    ];

    public function sharedExpense(): BelongsTo
    {
        return $this->belongsTo(SharedExpense::class);
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Housemate::class, 'debtor_housemate_id');
    }

    public function creditor(): BelongsTo
    {
        return $this->belongsTo(Housemate::class, 'creditor_housemate_id');
    }
}
