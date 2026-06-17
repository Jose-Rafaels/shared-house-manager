<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreAssignment extends Model
{
    protected $fillable = [
        'chore_id',
        'member_id',
        'assigned_for_date',
        'completed_at',
    ];

    protected $casts = [
        'assigned_for_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
