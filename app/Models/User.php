<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mysql';

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'prenom',
        'email',
        'password',
        'location',
        'phone',
        'about',
        'profile_photo',
        'is_blocked',
        'role',
    ];

    protected $attributes = [
        'role' => self::ROLE_USER,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_blocked' => 'boolean',
        'role' => 'string',
    ];

    public function getProfilePhotoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : asset('assets/img/default-profile.jpg');
    }

    public function getRawProfilePhotoAttribute()
    {
        return $this->attributes['profile_photo'] ?? null;
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(\App\Models\Shop\Product::class, 'user_product_favorites')
                    ->withTimestamps();
    }

    public function events()
    {
        return $this->belongsToMany(\App\Models\Event\Event::class, 'event_user')
                    ->withTimestamps()
                    ->withPivot('attendance_status');
    }

    // Get user's status for a specific event
    public function getAttendanceStatusForEvent($eventId)
    {
        $pivot = $this->events()->where('event_id', $eventId)->first();
        return $pivot?->pivot?->attendance_status ?? null;
    }

    // Scope for user's attended events
    public function scopeAttendedEvents($query)
    {
        return $query->whereHas('events', function ($q) {
            $q->wherePivot('attendance_status', 'attended');
        });
    }

    public function isAdmin(): bool
    {
        $role = $this->role ?? null;
        return $role !== null && Str::lower($role) === self::ROLE_ADMIN;
    }

    public function setRoleAttribute($value): void
    {
        $this->attributes['role'] = $value !== null ? Str::lower($value) : null;
    }
    
    public function statuteReactions()
    {
        return $this->hasMany(StatuteReaction::class);
    }

}
