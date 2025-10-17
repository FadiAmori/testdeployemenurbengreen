<?php


namespace App\Models\Event;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'location',
        'image',
        'is_published',
        'user_id',
        'category_id', 
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    // Scope for published events
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // Scope for upcoming events
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>', Carbon::now());
    }

    // Scope for attendees (e.g., attended only)
    public function scopeAttended($query)
    {
        return $query->whereHas('users', function ($q) {
            $q->wherePivot('attendance_status', 'attended');
        });
    }

    // Method to produce formatted status badge HTML for a given user
    public function statusBadge($userId = null)
    {
        if (!$userId) return '<span class="badge bg-light text-dark">Not Joined</span>';
        $status = $this->users()->where('user_id', $userId)->first()?->pivot?->attendance_status ?? null;
        return match ($status) {
            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'confirmed' => '<span class="badge bg-info">Confirmed</span>',
            'attended' => '<span class="badge bg-success">Attended</span>',
            'absent' => '<span class="badge bg-secondary">Absent</span>',
            default => '<span class="badge bg-light text-dark">Not Joined</span>'
        };
    }

    // Accessor for statusBadge attribute for current authenticated user
    public function getStatusBadgeAttribute()
    {
        return $this->statusBadge(auth()->id());
    }

    // Get user's status for a specific event (utility method)
    public function getAttendanceStatusForEvent($userId)
    {
        $pivot = $this->users()->where('user_id', $userId)->first();
        return $pivot?->pivot?->attendance_status ?? null;
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'event_user', 'event_id', 'user_id')
                    ->withTimestamps()
                    ->withPivot('attendance_status');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }
}