<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- ADDED: Needed for relationships

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['profile_picture_url']; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture_path', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accessor Method: Creates the 'profile_picture_url' field for the JSON response
    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture_path) {
            return Storage::url($this->profile_picture_path);
        }

        return null; 
    }

    // 🎯 NEW: Define the relationship to incidents filed by this user.
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'filer_id');
    }
}