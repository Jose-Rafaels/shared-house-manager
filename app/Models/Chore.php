<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rotation_start_date',
    ];

    protected $casts = [
        'rotation_start_date' => 'date',
    ];

    public function rotations(): HasMany
    {
        return $this->hasMany(ChoreRotation::class)->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ChoreAssignment::class)->orderByDesc('assigned_for_date');
    }
}
