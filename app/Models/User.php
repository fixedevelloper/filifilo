<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    const TYPE_ADMIN     = 1;
    const TYPE_VENDOR     = 2;
    const TYPE_CUSTOMER     = 3;
    const TYPE_SHIPPING     = 4;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'user_type', 'image_url','fcm_token'
    ];

    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }


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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
