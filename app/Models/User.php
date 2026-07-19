<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 's_users';

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
        'employee_id',
        'division_id',
        'email',
        'password',
        'first_name',
        'last_name',
        'date_of_birth',
        'is_admin',
        'is_active',
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
        'date_of_birth' => 'date',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the division this user belongs to.
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Get the reservations made by the user.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    /**
     * Get the rooms created by this user.
     */
    public function createdRooms()
    {
        return $this->hasMany(Room::class, 'created_by');
    }

    /**
     * Get all registered FCM device tokens for this user.
     */
    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class, 'user_id');
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Get user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Alias so packages reading `name` (e.g. AdminLTE navbar) work.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Canonical user payload for auth responses (login, me) — API and web share this shape.
     */
    public function toAuthArray(): array
    {
        $this->loadMissing('division:id,name,code');

        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
            'division_id' => $this->division_id,
            'division' => $this->division?->only(['id', 'name', 'code']),
            'is_admin' => $this->is_admin,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Check if user can approve reservations.
     */
    public function canApprove(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Specifies the FCM token for push notifications.
     * Returns all device tokens so every logged-in device receives the notification.
     *
     * @return string|array<string>|null
     */
    public function routeNotificationForFcm(): array|string|null
    {
        $tokens = $this->fcmTokens()->pluck('token')->all();
        return empty($tokens) ? null : $tokens;
    }

    /**
     * Build a formatted employee ID like EMP-YYYY-#####.
     */
    public static function formatEmployeeId(int $year, int $sequence, string $prefix = 'EMP'): string
    {
        $prefix = strtoupper($prefix);

        return sprintf('%s-%04d-%05d', $prefix, $year, $sequence);
    }
}
