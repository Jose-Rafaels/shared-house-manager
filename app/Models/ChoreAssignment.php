<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'chore_id',
        'housemate_id',
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

    public function housemate(): BelongsTo
    {
        return $this->belongsTo(Housemate::class);
    }
}
