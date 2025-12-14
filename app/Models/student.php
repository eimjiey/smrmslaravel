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
    ];

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'student_id', 'student_number');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}
