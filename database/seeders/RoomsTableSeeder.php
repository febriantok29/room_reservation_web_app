<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user ID for created_by field
        $adminId = DB::table('s_users')->where('role', 'admin')->first()->id ?? null;

        $rooms = [
            [
                'id' => (string) Str::uuid7(),
                'name' => 'R. Serbaguna',
                'location' => 'Lantai 1',
                'description' => 'Ruang serbaguna untuk berbagai keperluan meeting dan acara. Dilengkapi dengan proyektor, whiteboard, dan sistem audio.',
                'capacity' => 35,
                'is_maintenance' => false,
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'R. Meeting Utama',
                'location' => 'Lantai 2',
                'description' => 'Ruang meeting utama dengan fasilitas lengkap termasuk video conference, AC, dan koneksi internet berkecepatan tinggi.',
                'capacity' => 25,
                'is_maintenance' => false,
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'R. Eksekutif',
                'location' => 'Lantai 3',
                'description' => 'Ruang meeting eksekutif untuk rapat tingkat manajemen. Tersedia coffee station dan tata cahaya yang dapat diatur.',
                'capacity' => 15,
                'is_maintenance' => false,
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'R. Diskusi',
                'location' => 'Lantai 4',
                'description' => 'Ruang diskusi yang nyaman untuk brainstorming dan meeting tim. Dilengkapi dengan whiteboard interaktif.',
                'capacity' => 20,
                'is_maintenance' => false,
            ],
        ];

        foreach ($rooms as $room) {
            DB::table('m_rooms')->insert(array_merge($room, [
                'created_at' => now(),
                'created_by' => $adminId,
            ]));
        }

        $this->command->info('Rooms seeded successfully!');
        $this->command->info('4 meeting rooms created (1 per floor, from Lantai 1 to Lantai 4)');
    }
}
