<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseSplit extends Model
{
    protected $fillable = [
        'expense_id',
        'member_id',
        'amount_owed',
        'is_settled',
    ];

    protected $casts = [
        'is_settled' => 'boolean',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeSettled(Builder $query): Builder
    {
        return $query->where('is_settled', true);
    }

    public function scopeUnsettled(Builder $query): Builder
    {
        return $query->where('is_settled', false);
    }
}
