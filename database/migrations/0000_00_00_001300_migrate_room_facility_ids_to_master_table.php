<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('m_rooms', 'facility_ids')) {
            return;
        }

        $rooms = DB::table('m_rooms')->select('id', 'facility_ids')->get();

        foreach ($rooms as $room) {
            $decoded = json_decode((string) ($room->facility_ids ?? '[]'), true);

            if (!is_array($decoded) || empty($decoded)) {
                continue;
            }

            $facilityNames = collect($decoded)
                ->map(fn ($name) => str_replace(['_', '-'], ' ', (string) $name))
                ->map(fn (string $name) => Str::of($name)->squish()->lower()->toString())
                ->filter(fn (string $name) => $name !== '')
                ->unique()
                ->values();

            foreach ($facilityNames as $facilityName) {
                $slug = Str::slug($facilityName, '_');

                if ($slug === '') {
                    continue;
                }

                $existingFacility = DB::table('m_facilities')->where('slug', $slug)->first();

                $facilityId = $existingFacility?->id;

                if (!$facilityId) {
                    $facilityId = (string) Str::uuid7();

                    DB::table('m_facilities')->insert([
                        'id' => $facilityId,
                        'name' => Str::of($facilityName)->title()->toString(),
                        'slug' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('m_room_facilities')->updateOrInsert(
                    ['room_id' => $room->id, 'facility_id' => $facilityId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
