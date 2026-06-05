<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Housemate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function billParticipants(): HasMany
    {
        return $this->hasMany(BillParticipant::class);
    }

    public function billPayments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function cashFundEntries(): HasMany
    {
        return $this->hasMany(CashFundEntry::class);
    }

    public function paidSharedExpenses(): HasMany
    {
        return $this->hasMany(SharedExpense::class, 'payer_housemate_id');
    }

    public function sharedExpenseParticipants(): HasMany
    {
        return $this->hasMany(SharedExpenseParticipant::class);
    }
}
