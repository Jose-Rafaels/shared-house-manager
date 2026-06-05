<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'amount',
        'due_date',
        'billing_month',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'billing_month' => 'date',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(BillParticipant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }
}
