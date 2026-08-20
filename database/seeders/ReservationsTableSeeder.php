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
        $users = DB::table('s_users')->where('is_admin', false)->get();
        $rooms = DB::table('m_rooms')->get();

        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Users or rooms not found! Please seed users and rooms first.');
            return;
        }

        // All dates are relative to "now" so the calendar is always filled
        // around the month the seeder is run (bulan ke-3 = bulan berjalan).
        $now = now();
        $monthStart = $now->copy()->startOfMonth();

        // Historis: 2 bulan ke belakang
        $histStart = $monthStart->copy()->subMonths(2);
        $histEnd = $monthStart->copy()->subDay();
        $histDays = max(1, $histStart->diffInDays($histEnd));

        // Future: sekarang .. +3 bulan + ~12 hari
        $futureEnd = $monthStart->copy()->addMonths(3)->addDays(12);
        $futureDays = max(1, $now->diffInDays($futureEnd));

        $userIndex = 0;
        $roomIndex = 0;

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

        // Tanggal acak dalam rentang, sambil menghindari weekend.
        $randomDate = function (Carbon $start, int $spanDays) {
            $date = $start->copy()->addDays(rand(0, $spanDays));
            while ($date->isWeekend()) {
                $date->addDay();
            }
            return $date;
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

        $reservations = [];

        // ========================================
        // [1] COMPLETED — historis (2 bulan ke belakang)
        // ========================================
        for ($i = 0; $i < 90; $i++) {
            $date = $randomDate($histStart, $histDays);
            $startHour = rand(8, 15);
            $duration = rand(2, 4);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $date->copy()->setTime($startHour, 0, 0),
                'end_time' => $date->copy()->setTime($startHour + $duration, 0, 0),
                'purpose' => $purposes[$i % count($purposes)],
                'visitor_count' => rand(4, 15),
                'status' => 'completed',
            ];
        }

        // ========================================
        // [2] CANCELLED — historis
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

        for ($i = 0; $i < 22; $i++) {
            $date = $randomDate($histStart, $histDays);
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
        // [3] REJECTED — historis
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

        for ($i = 0; $i < 18; $i++) {
            $date = $randomDate($histStart, $histDays);
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
        // [4] APPROVED (PAST) — target cron auto-complete
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
        // [5] PENDING (PAST) — target cron auto-cancel
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
        // [6] TODAY — 13:00 (approved), 15:00 (pending), 17:00 (approved)
        // ========================================
        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->setTime(13, 0, 0),
            'end_time' => $now->copy()->setTime(15, 0, 0),
            'purpose' => 'Rapat koordinasi mingguan tim IT',
            'visitor_count' => 8,
            'status' => 'approved',
        ];
        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->setTime(15, 0, 0),
            'end_time' => $now->copy()->setTime(17, 0, 0),
            'purpose' => 'Review budget Q2 divisi keuangan',
            'visitor_count' => 6,
            'status' => 'pending',
        ];
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
        // [7] APPROVED (FUTURE) — now .. +3 bulan + 12 hari
        // ========================================
        for ($i = 0; $i < 30; $i++) {
            $date = $randomDate($now, $futureDays);
            $startHour = rand(9, 15);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $date->copy()->setTime($startHour, 0, 0),
                'end_time' => $date->copy()->setTime($startHour + 3, 0, 0),
                'purpose' => $purposes[rand(0, count($purposes) - 1)],
                'visitor_count' => rand(6, 12),
                'status' => 'approved',
            ];
        }

        // ========================================
        // [8] PENDING (FUTURE) — now .. +3 bulan + 12 hari
        // ========================================
        for ($i = 0; $i < 25; $i++) {
            $date = $randomDate($now, $futureDays);
            $startHour = rand(10, 16);

            $reservations[] = [
                'user_id' => $nextUser(),
                'room_id' => $nextRoom(),
                'start_time' => $date->copy()->setTime($startHour, 0, 0),
                'end_time' => $date->copy()->setTime($startHour + 2, 0, 0),
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

        $this->command->info('Reservations seeded: ' . count($reservations) . ' total (date-relative to seed run)');
        $this->command->info('  - 90 completed (2 bulan ke belakang)');
        $this->command->info('  - 22 cancelled (historis)');
        $this->command->info('  - 18 rejected (historis)');
        $this->command->info('  - 10 approved (past - cron test)');
        $this->command->info('  - 10 pending (past - cron test)');
        $this->command->info('  - 3 TODAY (13:00, 15:00, 17:00)');
        $this->command->info('  - 30 approved (future, now .. +3bln+12d)');
        $this->command->info('  - 25 pending (future, now .. +3bln+12d)');
        $this->command->info('');
        $this->command->warn('Run: php artisan schedule:work to test cron job automation.');
    }
}
