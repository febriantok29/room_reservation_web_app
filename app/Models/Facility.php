<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Facility extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_facilities';

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
        'id',
        'name',
        'slug',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot',
    ];

    /**
     * Get the rooms that have this facility.
     */
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'm_room_facilities', 'facility_id', 'room_id')
            ->withTimestamps();
    }

    /**
     * Parse comma-separated facility input.
     */
    public static function parseInput(?string $rawInput): array
    {
        if (!$rawInput) {
            return [];
        }

        return self::normalizeNames(explode(',', $rawInput));
    }

    /**
     * Normalize facility names into lowercase unique values.
     */
    public static function normalizeNames(array $names): array
    {
        return collect($names)
            ->map(function ($name) {
                $name = (string) $name;
                // If it's a UUID, keep it as is
                if (Str::isUuid($name)) {
                    return $name;
                }
                // Otherwise, normalize spaces and separators
                return str_replace(['_', '-'], ' ', $name);
            })
            ->map(fn (string $name) => Str::isUuid($name) ? $name : Str::of($name)->squish()->lower()->toString())
            ->filter(fn (string $name) => $name !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalize to slugs for querying by facility filter.
     */
    public static function normalizeSlugs(array $inputs): array
    {
        return collect(self::normalizeNames($inputs))
            ->map(fn (string $name) => Str::slug($name, '_'))
            ->filter(fn (string $slug) => $slug !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resolve facility IDs from user-provided names/slugs, creating missing facilities.
     */
    public static function resolveIds(array $inputs): array
    {
        $ids = [];

        foreach (self::normalizeNames($inputs) as $input) {
            // 1. Check if it's an existing UUID
            if (Str::isUuid($input)) {
                $existing = self::query()->find($input);
                if ($existing) {
                    $ids[] = $existing->id;
                    continue;
                }
            }

            // 2. Otherwise treat as name/slug
            $slug = Str::slug($input, '_');
            if ($slug === '') {
                continue;
            }

            $facility = self::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'id' => (string) Str::uuid7(),
                    'name' => Str::of($input)->title()->toString(),
                ]
            );

            $ids[] = $facility->id;
        }

        return array_values(array_unique($ids));
    }
}
