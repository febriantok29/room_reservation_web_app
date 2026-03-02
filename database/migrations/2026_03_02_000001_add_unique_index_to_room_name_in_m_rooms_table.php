<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateNames = DB::table('m_rooms')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicateNames as $name) {
            $rooms = DB::table('m_rooms')
                ->where('name', $name)
                ->orderByRaw('deleted_at IS NULL DESC')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id', 'name']);

            $keepFirst = true;
            foreach ($rooms as $room) {
                if ($keepFirst) {
                    $keepFirst = false;
                    continue;
                }

                $suffix = ' #' . substr((string) $room->id, 0, 8);
                $trimmedBase = mb_substr((string) $name, 0, 100 - mb_strlen($suffix));
                $newName = $trimmedBase . $suffix;

                DB::table('m_rooms')
                    ->where('id', $room->id)
                    ->update(['name' => $newName]);
            }
        }

        Schema::table('m_rooms', function (Blueprint $table) {
            $table->unique('name', 'm_rooms_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_rooms', function (Blueprint $table) {
            $table->dropUnique('m_rooms_name_unique');
        });
    }
};
