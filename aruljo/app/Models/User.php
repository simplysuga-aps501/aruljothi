<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Profile image (you can return a default image or use a DB field)
        public function adminlte_image()
        {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
            // Or return $this->profile_photo_url if you use Laravel Jetstream
        }

        // User description (optional)
        public function adminlte_desc()
        {
            return 'Admin'; // or $this->role, etc.
        }

        // Link to profile page
        public function adminlte_profile_url()
        {
            return route('profile.edit'); // adjust if using a different route
        }
}
