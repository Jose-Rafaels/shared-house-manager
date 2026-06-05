<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChoreRotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'chore_id',
        'housemate_id',
        'sort_order',
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
