<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStorageUrl;
use App\Services\ComplaintIdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomComplaint extends Model
{
    use SoftDeletes;
    use HasPublicStorageUrl;

    /**
     * The table associated with the model.
     */
    protected $table = 't_room_complaints';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'reservation_id',
        'reported_by',
        'room_id',
        'facility_id',
        'title',
        'description',
        'photo_path',
        'status',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'photo_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's serialized form.
     *
     * @var array<int, string>
     */
    protected $appends = ['photo_url'];

    /**
     * Auto-generate complaint ID before creating.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $complaint) {
            if (empty($complaint->id)) {
                $complaint->id = ComplaintIdGenerator::generate();
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    /**
     * Get the public URL of the complaint photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->publicStorageUrl($this->photo_path);
    }

    // ─── Query Scopes ─────────────────────────────────────────────────────────

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForRoom($query, string $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    public function scopeForReporter($query, string $userId)
    {
        return $query->where('reported_by', $userId);
    }

    // ─── Status Helpers ───────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Returns true when no further status transitions are possible.
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'rejected']);
    }
}
