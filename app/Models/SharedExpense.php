<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharedExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'payer_housemate_id',
        'amount',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Housemate::class, 'payer_housemate_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SharedExpenseParticipant::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(DebtSettlement::class);
    }
}
