<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User; 
use App\Models\Student; 
use App\Models\OffenseCategory;
use App\Models\Offense;

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
        'disciplinary_action',
    ];
    
    protected $with = ['student', 'offense', 'category'];

    protected $appends = [
        'full_name', 
        'specific_offense', 
        'offense_category'
    ];

    public function getFullNameAttribute(): string
    {
        if ($this->student) {
            $nameParts = [
                $this->student->first_name, 
                $this->student->middle_name, 
                $this->student->last_name
            ];
            return implode(' ', array_filter($nameParts));
        }
        return 'N/A';
    }

    public function getSpecificOffenseAttribute(): string
    {
        return $this->offense ? $this->offense->name : 'N/A';
    }

    public function getOffenseCategoryAttribute(): string
    {
        return $this->category ? $this->category->name : 'N/A';
    }

    public function filer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filer_id');
    }

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
}
