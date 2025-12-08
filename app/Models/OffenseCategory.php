<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OffenseCategory extends Model
{
    use HasFactory;
    protected $table = 'offense_categories';

    /**
     * Get the specific offenses belonging to this category.
     */
    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class, 'category_id');
    }

    /**
     * Get the incidents associated with this category.
     */
    public function incidents(): HasMany
    {
        // The incidents table uses 'category_id' as the foreign key
        return $this->hasMany(Incident::class, 'category_id');
    }
}