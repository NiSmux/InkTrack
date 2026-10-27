<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // JWTSubject: returns the unique identifier for the token subject
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    // JWTSubject: custom claims embedded in the token payload (role-based auth reads from here)
    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}