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
        $users = DB::table('s_users')->where('is_admin', false)->get();
        $rooms = DB::table('m_rooms')->get();

        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Users or rooms not found! Please seed users and rooms first.');
            return;
        }

        // Today: April 6, 2026
        // Date range: Jan 1, 2025 - Apr 30, 2026
        $now = now(); // April 6, 2026
        $startDate = Carbon::create(2025, 1, 1);
        $reservations = [];
        $userIndex = 0;
        $roomIndex = 0;

        // Helper functions to rotate users and rooms
        $nextUser = function () use (&$userIndex, $users) {
            $user = $users[$userIndex % count($users)];
            $userIndex++;
            return $user->id;
        };

        $nextRoom = function () use (&$roomIndex, $rooms) {
            $room = $rooms[$roomIndex % count($rooms)];
            $roomIndex++;
            return $room->id;
        };

        $purposes = [
            'Rapat koordinasi tim Marketing strategi Q1',
            'Workshop pelatihan software development',
            'Presentasi proposal klien baru',
            'Training internal tim HRD',
            'Rapat evaluasi kinerja bulanan',
            'Diskusi roadmap produk 2025',
            'Client meeting with potential investors',
            'Brainstorming session fitur aplikasi',
            'Town hall meeting seluruh karyawan',
            'Sprint planning meeting tim IT',
            'Budget review dengan divisi Finance',
            'Onboarding training karyawan baru',
            'Quarterly business review',
            'Product demo untuk stakeholders',
            'Leadership team strategy session',
            'Customer feedback review meeting',
            'Technical workshop CI/CD pipeline',
            'Annual performance review discussion',
            'Vendor negotiation meeting',
            'Crisis management simulation training',
            'Team building activities',
            'Product roadmap planning',
            'Client presentation deck review',
            'Monthly sales performance review',
            'Strategic planning session',
            'Innovation brainstorming session',
            'Vendor selection committee meeting',
            'Performance appraisal discussion',
            'Client onboarding session',
            'Project kickoff meeting',
        ];

        // ========================================
        // [1] COMPLETED — Historical data (100 reservations)
        // Spread from Jan 1, 2025 to March 31, 2026
        // ========================================
        $completedStartDate = Carbon::create(2025, 1, 1);
        $completedEndDate = Carbon::create(2026, 3, 31);
        $totalDaysCompleted = $completedStartDate->diffInDays($completedEndDate);

        for ($i = 0; $i < 100; $i++) {
            $randomDays = rand(0, $totalDaysCompleted);
            $date = $completedStartDate->copy()->addDays($randomDays);

            // Skip weekends
            while ($date->isWeekend()) {
                $date->addDay();
            }

            $startHour = rand(8, 15);
            $duration = rand(2, 4);
            $visitorCount = rand(4, 15);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $date->copy()->setTime($startHour, 0, 0),
                'end_time' => $date->copy()->setTime($startHour + $duration, 0, 0),
                'purpose' => $purposes[$i % count($purposes)],
                'visitor_count' => $visitorCount,
                'status' => 'completed',
            ];
        }

        // ========================================
        // [2] CANCELLED — Mixed throughout the year (20 reservations)
        // ========================================
        $cancelReasons = [
            'Rapat dibatalkan karena perubahan prioritas bisnis',
            'Peserta utama berhalangan hadir, reschedule diperlukan',
            'Kegiatan dialihkan ke format online via Zoom',
            'Pembatalan mendadak karena emergency meeting',
            'Rapat ditunda menunggu laporan keuangan final',
            'Tim tidak cukup siap untuk presentasi',
            'Klien membatalkan janji temu secara mendadak',
            'Konflik jadwal dengan rapat direksi',
        ];

        for ($i = 0; $i < 20; $i++) {
            // Spread across the entire period
            $randomDays = rand(10, 450); // Jan 2025 to Mar 2026
            $date = Carbon::create(2025, 1, 1)->addDays($randomDays);

            while ($date->isWeekend()) {
                $date->addDay();
            }

            $startHour = rand(9, 15);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $date->copy()->setTime($startHour, 0, 0),
                'end_time' => $date->copy()->setTime($startHour + 2, 0, 0),
                'purpose' => $cancelReasons[$i % count($cancelReasons)],
                'visitor_count' => rand(4, 10),
                'status' => 'cancelled',
            ];
        }

        // ========================================
        // [3] REJECTED — Mixed throughout the year (15 reservations)
        // ========================================
        $rejectReasons = [
            'Rapat ditolak: ruangan sudah dibooking untuk acara prioritas lebih tinggi',
            'Permintaan ditolak: informasi tidak lengkap dan pemohon tidak responsif',
            'Ditolak: jumlah peserta melebihi kapasitas ruangan yang diminta',
            'Pengajuan ditolak karena bentrok dengan maintenance ruangan',
            'Ditolak: waktu reservasi di luar jam operasional',
            'Permintaan tidak disetujui karena tujuan tidak jelas',
            'Ditolak: ruangan tidak sesuai dengan kebutuhan acara',
        ];

        for ($i = 0; $i < 15; $i++) {
            $randomDays = rand(15, 440);
            $date = Carbon::create(2025, 1, 1)->addDays($randomDays);

            while ($date->isWeekend()) {
                $date->addDay();
            }

            $startHour = rand(10, 16);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $date->copy()->setTime($startHour, 0, 0),
                'end_time' => $date->copy()->setTime($startHour + 3, 0, 0),
                'purpose' => $rejectReasons[$i % count($rejectReasons)],
                'visitor_count' => rand(5, 20),
                'status' => 'rejected',
            ];
        }

        // ========================================
        // [4] APPROVED (PAST) — For cron auto-complete testing (10 reservations)
        // ========================================
        for ($i = 0; $i < 10; $i++) {
            $daysAgo = rand(1, 5);
            $startHour = rand(9, 14);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $now->copy()->subDays($daysAgo)->setTime($startHour, 0, 0),
                'end_time' => $now->copy()->subDays($daysAgo)->setTime($startHour + 2, 0, 0),
                'purpose' => '[CRON TEST] Rapat divisi yang sudah disetujui (target auto-complete)',
                'visitor_count' => rand(5, 10),
                'status' => 'approved',
            ];
        }

        // ========================================
        // [5] PENDING (PAST) — For cron auto-cancel testing (10 reservations)
        // ========================================
        for ($i = 0; $i < 10; $i++) {
            $daysAgo = rand(1, 5);
            $startHour = rand(10, 15);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $now->copy()->subDays($daysAgo)->setTime($startHour, 0, 0),
                'end_time' => $now->copy()->subDays($daysAgo)->setTime($startHour + 2, 0, 0),
                'purpose' => '[CRON TEST] Rapat pending yang terlewat (target auto-cancel)',
                'visitor_count' => rand(3, 8),
                'status' => 'pending',
            ];
        }

        // ========================================
        // [6] TODAY — Ongoing/upcoming meetings (3 reservations at 13:00, 15:00, 17:00)
        // ========================================
        // 13:00 - approved, currently running
        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->setTime(13, 0, 0),
            'end_time' => $now->copy()->setTime(15, 0, 0),
            'purpose' => 'Rapat koordinasi mingguan tim IT',
            'visitor_count' => 8,
            'status' => 'approved',
        ];

        // 15:00 - pending approval
        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->setTime(15, 0, 0),
            'end_time' => $now->copy()->setTime(17, 0, 0),
            'purpose' => 'Review budget Q2 divisi keuangan',
            'visitor_count' => 6,
            'status' => 'pending',
        ];

        // 17:00 - approved
        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->setTime(17, 0, 0),
            'end_time' => $now->copy()->setTime(19, 0, 0),
            'purpose' => 'Presentasi hasil survey kepuasan pelanggan',
            'visitor_count' => 12,
            'status' => 'approved',
        ];

        // ========================================
        // [7] APPROVED (FUTURE) — Upcoming confirmed meetings (12 reservations)
        // ========================================
        for ($i = 0; $i < 12; $i++) {
            $daysAhead = rand(3, 24);
            $startHour = rand(9, 15);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $now->copy()->addDays($daysAhead)->setTime($startHour, 0, 0),
                'end_time' => $now->copy()->addDays($daysAhead)->setTime($startHour + 3, 0, 0),
                'purpose' => $purposes[rand(0, count($purposes) - 1)],
                'visitor_count' => rand(6, 12),
                'status' => 'approved',
            ];
        }

        // ========================================
        // [8] PENDING (FUTURE) — Awaiting approval (10 reservations)
        // ========================================
        for ($i = 0; $i < 10; $i++) {
            $daysAhead = rand(2, 20);
            $startHour = rand(10, 16);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $now->copy()->addDays($daysAhead)->setTime($startHour, 0, 0),
                'end_time' => $now->copy()->addDays($daysAhead)->setTime($startHour + 2, 0, 0),
                'purpose' => 'Rapat koordinasi menunggu persetujuan admin',
                'visitor_count' => rand(4, 10),
                'status' => 'pending',
            ];
        }

        // ========================================
        // INSERT ALL RESERVATIONS
        // ========================================
        foreach ($reservations as $data) {
            $idGenerator = new \App\Services\ReservationIdGenerator();
            $id = $idGenerator->generate($data['start_time']);

            DB::table('t_reservations')->insertOrIgnore([
                'id' => $id,
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'purpose' => $data['purpose'],
                'visitor_count' => $data['visitor_count'],
                'status' => $data['status'],
                'created_at' => $data['start_time']->copy()->subDays(rand(1, 7)),
                'created_by' => $data['user_id'],
                'updated_at' => $data['start_time']->copy()->subDays(rand(1, 7)),
                'updated_by' => $data['user_id'],
                'deleted_at' => null,
                'deleted_by' => null,
            ]);
        }

        $this->command->info('Reservations seeded: ' . count($reservations) . ' total');
        $this->command->info('  - 100 completed (historical data Jan 2025 - Mar 2026)');
        $this->command->info('  - 20 cancelled (distributed throughout year)');
        $this->command->info('  - 15 rejected (distributed throughout year)');
        $this->command->info('  - 10 approved (past - cron test targets)');
        $this->command->info('  - 10 pending (past - cron test targets)');
        $this->command->info('  - 3 TODAY at 13:00, 15:00, 17:00');
        $this->command->info('  - 12 approved (future)');
        $this->command->info('  - 10 pending (future)');
        $this->command->info('');
        $this->command->warn('Run: php artisan schedule:work to test cron job automation.');
    }
}

