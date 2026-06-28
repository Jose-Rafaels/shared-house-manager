<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function expensesAsPayer(): HasMany
    {
        return $this->hasMany(Expense::class, 'payer_id');
    }

    public function expenseSplits(): HasMany
    {
        return $this->hasMany(ExpenseSplit::class, 'member_id');
    }

    public function settlementsFrom(): HasMany
    {
        return $this->hasMany(Settlement::class, 'from_member_id');
    }

    public function settlementsTo(): HasMany
    {
        return $this->hasMany(Settlement::class, 'to_member_id');
    }

    public function choreAssignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class, 'member_id');
    }
}
