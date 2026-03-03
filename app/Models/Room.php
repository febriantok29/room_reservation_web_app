<?php

namespace App\Models;

use App\Services\RoomIdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_rooms';

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
        'name',
        'floor',
        'description',
        'capacity',
        'is_maintenance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'is_maintenance' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model and register events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (empty($room->id)) {
                $room->id = RoomIdGenerator::generate($room->floor);
            }
        });
    }

    /**
     * Get the reservations for the room.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'room_id');
    }

    /**
     * Get the facilities available in the room.
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'm_room_facilities', 'room_id', 'facility_id')
            ->withTimestamps();
    }

    /**
     * Get the user who created the room.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the room.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the room.
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include available rooms (not in maintenance).
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_maintenance', false);
    }

    /**
     * Scope a query to only include rooms by floor.
     */
    public function scopeByFloor($query, int $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope a query to only include rooms with minimum capacity.
     */
    public function scopeMinCapacity($query, int $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }

    /**
     * Scope a query to only include rooms that contain all required facilities.
     */
    public function scopeWithFacilities($query, array $facilityIds)
    {
        foreach (Facility::normalizeSlugs($facilityIds) as $facilitySlug) {
            $query->whereHas('facilities', function ($facilityQuery) use ($facilitySlug) {
                $facilityQuery->where('slug', $facilitySlug);
            });
        }

        return $query;
    }
}
