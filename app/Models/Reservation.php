<?php

namespace App\Models;

use App\Helpers\TimezoneHelper;
use App\Services\ReservationIdGenerator;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_reservations';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'start_time',
        'end_time',
        'purpose',
        'visitor_count',
        'with_snack',
        'with_lunch',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'visitor_count' => 'integer',
        'with_snack' => 'boolean',
        'with_lunch' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get start time in local timezone.
     */
    public function getStartTimeLocalAttribute()
    {
        return $this->start_time?->setTimezone($this->getLocalTimezone());
    }

    /**
     * Get end time in local timezone.
     */
    public function getEndTimeLocalAttribute()
    {
        return $this->end_time?->setTimezone($this->getLocalTimezone());
    }

    /**
     * Get local timezone for the current context.
     * For web requests, this could be dynamic based on user/browser.
     * For API, remains UTC.
     */
    protected function getLocalTimezone(): string
    {
        return TimezoneHelper::getLocalTimezone();
    }

    /**
     * Boot the model and register events.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate ID when creating new reservation
        static::creating(function ($reservation) {
            if (empty($reservation->id)) {
                $reservation->id = ReservationIdGenerator::generate();
            }
        });
    }

    /**
     * Get the user who made the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the room being reserved.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the user who created the reservation.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the reservation.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include reservations with a given status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved reservations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include active reservations (pending or approved).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Scope a query to only include upcoming reservations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Scope a query to only include past reservations.
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    /**
     * Scope a query to only include reservations within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include reservations for a specific room.
     */
    public function scopeForRoom($query, string $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope a query to only include reservations for a specific user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if reservation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if reservation is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if reservation is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if reservation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if reservation is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutes(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get duration in hours.
     */
    public function getDurationInHours(): float
    {
        return round($this->start_time->diffInHours($this->end_time, true), 2);
    }

    /**
     * Check if reservation is in progress.
     */
    public function isInProgress(): bool
    {
        $now = now();
        return $this->start_time <= $now && $this->end_time > $now && $this->isApproved();
    }

    /**
     * Check if reservation is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time > now() && in_array($this->status, ['pending', 'approved']);
    }

    /**
     * Readable Indonesian label for start time.
     */
    public function getStartTimeLabelAttribute(): string
    {
        return $this->formatReadableDateTime($this->start_time_local);
    }

    /**
     * Readable Indonesian label for end time.
     */
    public function getEndTimeLabelAttribute(): string
    {
        return $this->formatReadableDateTime($this->end_time_local);
    }

    /**
     * Format datetime to Indonesian readable text.
     */
    private function formatReadableDateTime(?CarbonInterface $dateTime): string
    {
        if (!$dateTime) {
            return '-';
        }

        return $dateTime->locale('id')->translatedFormat('l, d F Y H:i');
    }
}
