<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;
    
    // Assumes 'id' is the primary key and 'programs' is the table name.

    /**
     * Get the students enrolled in this program.
     */
    public function students(): HasMany
    {
        // program_id (FK on students table) links to id (PK on programs table)
        return $this->hasMany(Student::class, 'program_id');
    }
}