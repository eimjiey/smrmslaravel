<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    // Table name (optional if it matches plural of model)
    protected $table = 'students';

    // Primary key
    protected $primaryKey = 'student_id';

    // Auto-increment (true since you used $table->id())
    public $incrementing = true;

    // Primary key type
    protected $keyType = 'int';

    // Mass assignable fields
    protected $fillable = [
        'student_number',
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

    // Relationships (optional: if you link incidents, etc.)
    public function incidents()
    {
        return $this->hasMany(Incident::class, 'student_id', 'student_id');
    }
}
