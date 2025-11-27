<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- ADDED

class Incident extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // 🎯 NEW: Added filer_id so the store method can save it
        'filer_id', 
        'student_id',
        'full_name',
        'program',
        'year_level',
        'section',
        'date_of_incident',
        'time_of_incident',
        'location',
        'offense_category',
        'specific_offense',
        'description',
        'status',
        // Fields for optimization
        'recommendation', 
        'action_taken',
    ];

    // 🎯 NEW: Define the inverse relationship (Incident belongs to a Filer/User)
    public function filer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filer_id');
    }
}