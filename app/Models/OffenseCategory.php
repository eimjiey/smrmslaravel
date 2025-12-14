<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OffenseCategory extends Model
{
    use HasFactory;
    protected $table = 'offense_categories';

    public function offenses(): HasMany
    {
        return $this->hasMany(Offense::class, 'category_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'category_id');
    }
}
