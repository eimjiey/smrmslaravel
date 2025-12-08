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

    /**
     * Get the category this offense belongs to.
     */
    public function category(): BelongsTo
    {
        // category_id (FK on offenses table) links to id (PK on offense_categories table)
        return $this->belongsTo(OffenseCategory::class, 'category_id');
    }

    /**
     * Get the incidents associated with this specific offense.
     */
    public function incidents(): HasMany
    {
        // The incidents table uses 'specific_offense_id' as the foreign key
        return $this->hasMany(Incident::class, 'specific_offense_id');
    }
}