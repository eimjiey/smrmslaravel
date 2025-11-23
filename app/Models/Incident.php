<?php
// app/Models/Incident.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;
    
    // Add all fields that are allowed to be mass-assigned from the form data
    protected $fillable = [
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
        'status', // Optionally include if you want to set it on creation
    ];
}