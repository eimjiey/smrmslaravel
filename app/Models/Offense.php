<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offense extends Model
{
    use HasFactory;
    protected $table = 'offenses';

    public function category(): BelongsTo
    {
        return $this->belongsTo(OffenseCategory::class, 'category_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'specific_offense_id');
    }
}
