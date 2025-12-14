<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['profile_picture_url']; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture_path', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture_path) {
            return Storage::url($this->profile_picture_path);
        }

        return null; 
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'filer_id');
    }
}
