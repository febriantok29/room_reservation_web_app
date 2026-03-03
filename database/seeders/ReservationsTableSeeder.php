<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReservationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users and rooms for reservations
        $users = DB::table('s_users')->where('is_admin', false)->limit(5)->get();
        $rooms = DB::table('m_rooms')->get();

        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Users or rooms not found! Please seed users and rooms first.');
            return;
        }

        // Base date: March 2, 2026 (today)
        $today = Carbon::create(2026, 3, 2, 0, 0, 0);

        $reservations = [
            // Past reservations (3 sebelum hari ini)
            [
                'user_id' => $users[0]->id,
                'room_id' => $rooms[0]->id,
                'start_time' => $today->copy()->subDays(4)->setTime(9, 0, 0), // Feb 26, 09:00
                'end_time' => $today->copy()->subDays(4)->setTime(11, 0, 0),   // Feb 26, 11:00
                'purpose' => 'Rapat tim marketing untuk membahas strategi Q1 2026',
                'visitor_count' => 8,
                'status' => 'completed',
            ],
            [
                'user_id' => $users[1]->id,
                'room_id' => $rooms[1]->id,
                'start_time' => $today->copy()->subDays(3)->setTime(13, 0, 0), // Feb 27, 13:00
                'end_time' => $today->copy()->subDays(3)->setTime(15, 0, 0),   // Feb 27, 15:00
                'purpose' => 'Workshop pelatihan software baru untuk tim IT',
                'visitor_count' => 12,
                'status' => 'completed',
            ],
            [
                'user_id' => $users[2]->id,
                'room_id' => $rooms[2]->id,
                'start_time' => $today->copy()->subDays(2)->setTime(10, 0, 0), // Feb 28, 10:00
                'end_time' => $today->copy()->subDays(2)->setTime(12, 0, 0),   // Feb 28, 12:00
                'purpose' => 'Meeting dengan klien untuk presentasi proposal proyek',
                'visitor_count' => 6,
                'status' => 'completed',
            ],

            // Future reservations (2 setelah hari ini)
            [
                'user_id' => $users[3]->id,
                'room_id' => $rooms[0]->id,
                'start_time' => $today->copy()->addDays(1)->setTime(14, 0, 0), // Mar 3, 14:00
                'end_time' => $today->copy()->addDays(1)->setTime(16, 0, 0),   // Mar 3, 16:00
                'purpose' => 'Brainstorming sesi untuk kampanye produk baru',
                'visitor_count' => 10,
                'status' => 'approved',
            ],
            [
                'user_id' => $users[4]->id,
                'room_id' => $rooms[3]->id,
                'start_time' => $today->copy()->addDays(2)->setTime(9, 0, 0),  // Mar 4, 09:00
                'end_time' => $today->copy()->addDays(2)->setTime(11, 0, 0),   // Mar 4, 11:00
                'purpose' => 'Review kinerja tim dan perencanaan target bulanan',
                'visitor_count' => 15,
                'status' => 'approved',
            ],
        ];

        $reservationCount = 1;
        foreach ($reservations as $reservation) {
            // Generate reservation ID: RSV-YYYYMMDD-XX
            $date = Carbon::parse($reservation['start_time'])->format('Ymd');
            $id = sprintf('RSV-%s-%02d', $date, $reservationCount);

            DB::table('t_reservations')->insert([
                'id' => $id,
                'user_id' => $reservation['user_id'],
                'room_id' => $reservation['room_id'],
                'start_time' => $reservation['start_time'],
                'end_time' => $reservation['end_time'],
                'purpose' => $reservation['purpose'],
                'visitor_count' => $reservation['visitor_count'],
                'status' => $reservation['status'],
                'created_at' => $reservation['start_time']->copy()->subDays(7), // Created 7 days before reservation
                'created_by' => $reservation['user_id'],
            ]);

            $reservationCount++;
        }

        $this->command->info('Reservations seeded successfully!');
        $this->command->info('5 reservations created:');
        $this->command->info('  - 3 past reservations (completed)');
        $this->command->info('  - 2 future reservations (approved)');
    }
}
