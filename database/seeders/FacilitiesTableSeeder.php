<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            ['name' => 'Proyektor', 'slug' => 'proyektor'],
            ['name' => 'Whiteboard', 'slug' => 'whiteboard'],
            ['name' => 'Audio System', 'slug' => 'audio_system'],
            ['name' => 'WiFi', 'slug' => 'wifi'],
            ['name' => 'Video Conference', 'slug' => 'video_conference'],
            ['name' => 'AC', 'slug' => 'ac'],
            ['name' => 'Display', 'slug' => 'display'],
            ['name' => 'Coffee Station', 'slug' => 'coffee_station'],
            ['name' => 'Dispenser', 'slug' => 'dispenser'],
            ['name' => 'Flip Chart', 'slug' => 'flip_chart'],
            ['name' => 'Sound System', 'slug' => 'sound_system'],
            ['name' => 'Microphone', 'slug' => 'microphone'],
        ];

        foreach ($facilities as $facility) {
            DB::table('m_facilities')->insert([
                'id' => (string) Str::uuid7(),
                'name' => $facility['name'],
                'slug' => $facility['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Facilities seeded successfully!');
        $this->command->info('12 facilities created');
    }
}
