<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'assigned_to_member_id',
        'assigned_for_date',
    ];

    protected $casts = [
        'assigned_for_date' => 'date',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'assigned_to_member_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class)->orderByDesc('assigned_for_date');
    }
}
