<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'logins_histories';
    
    protected $fillable = [
        'user_id',
        'email_attempted',
        'device',
        'status',
    ];
}
