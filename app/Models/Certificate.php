<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'recipient_name',
        'title',
        'certificate_number',
        'issued_at',
        'notes',
    ];

    protected $dates = [
        'issued_at',
    ];
}
