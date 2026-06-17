<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    protected $fillable = [
        'payer_id',
        'category_id',
        'amount',
        'description',
        'expense_date',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'payer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function splits(): HasMany
    {
        return $this->hasMany(ExpenseSplit::class);
    }
}
