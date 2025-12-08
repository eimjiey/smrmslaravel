<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// CRITICAL: Ensure all necessary models are imported
use App\Models\User; 
use App\Models\Student; 
use App\Models\OffenseCategory;
use App\Models\Offense; // <-- Ensure this is imported

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filer_id', 
        'student_id',
        'category_id',      
        'specific_offense_id', 
        'date_of_incident',
        'time_of_incident',
        'location',
        'description',
        'status',
        'disciplinary_action', // Use disciplinary_action for DB column
    ];
    
    // Eager load relationships by default for efficiency
    protected $with = ['student', 'offense', 'category'];

    // Append virtual attributes to the JSON output for the frontend
    protected $appends = [
        'full_name', 
        'specific_offense', 
        'offense_category'
    ];
    
    // NOTE: The 'action_taken' column from the DB is accessed via 'disciplinary_action' 
    // in the controller's logic. If you need 'action_taken' in your model, ensure it's mapped.
    
    // ==================================================================================
    // --- Accessor Methods (Dynamically Computed Fields for Vue) ---
    // ==================================================================================

    /**
     * Accessor for the 'full_name' field (used by Vue).
     * Combines student's name parts using the nullsafe operator.
     */
    public function getFullNameAttribute(): string
    {
        // Check if the student relationship is loaded and exists
        if ($this->student) {
            $nameParts = [
                $this->student->first_name, 
                $this->student->middle_name, 
                $this->student->last_name
            ];
            // Filter out null/empty values and return concatenated string
            return implode(' ', array_filter($nameParts));
        }
        return 'N/A';
    }

    /**
     * Accessor for the 'specific_offense' field (used by Vue).
     */
    public function getSpecificOffenseAttribute(): string
    {
        // Safely access the related offense's name
        return $this->offense ? $this->offense->name : 'N/A';
    }

    /**
     * Accessor for the 'offense_category' field (used by Vue).
     */
    public function getOffenseCategoryAttribute(): string
    {
        // Safely access the related category's name
        return $this->category ? $this->category->name : 'N/A';
    }

    // ==================================================================================
    // --- Relationship Methods ---
    // ==================================================================================

    public function filer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filer_id');
    }

    /**
     * Relates Incident.student_id (FK) to Student.student_number (Unique Field)
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_number');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OffenseCategory::class, 'category_id');
    }

    public function offense(): BelongsTo
    {
        return $this->belongsTo(Offense::class, 'specific_offense_id');
    }

    // NOTE: The getIncidentsWithFullDetails method should be in the IncidentController, 
    // not the model. Removed for clean model definition.
}