<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_name', 
        'certificate_number',
        'issued_date', 
        'student_id',
        'program_grade',
        'offense_type',
        'date_of_incident',
        'disciplinary_action',
        'status',
        // REMOVED: 'student_name' field as it doesn't exist in the database
        'school_name',
        'school_location',
        'official_name',
        'official_position',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'date_of_incident' => 'date',
    ];
}