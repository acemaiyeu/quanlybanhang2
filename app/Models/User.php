<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\City;
use App\Models\District;
use App\Models\Role;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id'
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id')->select('id', 'code', 'name');;
    }

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id')->select('id', 'code', 'name');
    }

    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id')->select('id', 'code', 'name');;
    }

    public function ward()
    {
        return $this->hasOne(Ward::class, 'id', 'ward_id')->select('id', 'code', 'name');;
    }
}
