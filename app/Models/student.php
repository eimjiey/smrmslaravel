<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'students';

    // Primary key (This is the auto-incrementing integer ID)
    protected $primaryKey = 'student_id';

    // Auto-increment 
    public $incrementing = true;

    // Primary key type
    protected $keyType = 'int';

    // Mass assignable fields
    protected $fillable = [
        'student_number', // The unique ID used for lookups (e.g., 23-0001)
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'program',
        'year_level',
        'section',
        'contact_number',
        'email',
        'address',
        'guardian_name',
        'guardian_contact',
    ];

    /**
     * Get the incident reports associated with the student.
     */
    public function incidents()
    {
        // NOTE: If the 'student_id' column in the incidents table holds the
        // 'student_number' (e.g., 23-0001) and not the primary key 'id', 
        // you should adjust the foreign key relationship if needed.
        // Based on your Incident model using 'student_id' as the unique number:
        return $this->hasMany(Incident::class, 'student_id', 'student_number');
    }
}