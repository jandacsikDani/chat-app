<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Friend;
use Laravel\Pail\File;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function scopeActive($query){
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeSearch($query, $search){
        if(!$search) return $query;

        return $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
            ->onWhere('email', 'LIKE', "%{$search}%");
        });
    }

    public function friends(){
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
        ->wherePivot('status', 'accepted')
        ->withPivot('status')
        ->withTimestamps();
    }

    public function friendRequest(){
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
        ->wherePivot('status', 'pending')
        ->withPivot('status')
        ->withTimestamps();
    }

    public function sentFriendRequests(){
        return $this->hasMany(Friend::class, 'user_id')->where('status', 'pending');
    }

    public function receivedFriendRequests(){
        return $this->hasMany(Friend::class, 'friend_id')->where('status', 'pending');;
    }
}
