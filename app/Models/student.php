<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';
    
    // **CRITICAL FIX:** Align primary key name with the migration definition.
    protected $primaryKey = 'student_id'; 
    
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'student_number', 
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'program_id', 
        'year_level',
        'section',
        'contact_number',
        'email',
        'address',
        'guardian_name',
        'guardian_contact',
        // Removed 'program' field from fillable array as it is now derived from 'program_id'
    ];

    /**
     * Get the incident reports associated with the student.
     */
    public function incidents(): HasMany
    {
        // student_id (FK on incidents table) links to student_number (Unique Key on students table)
        return $this->hasMany(Incident::class, 'student_id', 'student_number');
    }

    /**
     * Get the program the student belongs to.
     */
    public function program(): BelongsTo
    {
        // program_id (FK on students table) links to id (PK on programs table)
        return $this->belongsTo(Program::class, 'program_id');
    }
}