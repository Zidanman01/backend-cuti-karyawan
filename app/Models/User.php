<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Penting untuk Token API

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',          // Baru
        'google_id',     // Baru
        'avatar',        // Baru
        'leave_quota',   // Baru
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id', // Kita sembunyikan ID google dari output JSON agar rapi
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'leave_quota' => 'integer',
        ];
    }

    // Relasi: Satu User punya banyak Request Cuti
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}