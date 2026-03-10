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

        // Use current time as baseline so data is always fresh relative to seeding time
        $now = now();

        $reservations = [];
        $userIndex = 0;
        $roomIndex = 0;

        // Helper lambda untuk rotate users dan rooms
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

        // ====== [1] COMPLETED — Sudah selesai, status benar (4 data) ======
        // Reservasi di masa lalu yang memang sudah `completed` dengan benar.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(30)->setTime(9, 0, 0),
            'end_time' => $now->copy()->subDays(30)->setTime(11, 0, 0),
            'purpose' => 'Rapat tim marketing strategi Q1',
            'visitor_count' => 8,
            'status' => 'completed',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(20)->setTime(13, 0, 0),
            'end_time' => $now->copy()->subDays(20)->setTime(15, 0, 0),
            'purpose' => 'Workshop pelatihan software tim IT',
            'visitor_count' => 12,
            'status' => 'completed',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(14)->setTime(10, 0, 0),
            'end_time' => $now->copy()->subDays(14)->setTime(12, 0, 0),
            'purpose' => 'Presentasi proposal klien baru',
            'visitor_count' => 6,
            'status' => 'completed',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(7)->setTime(9, 30, 0),
            'end_time' => $now->copy()->subDays(7)->setTime(11, 30, 0),
            'purpose' => 'Sesi training internal tim HRD',
            'visitor_count' => 10,
            'status' => 'completed',
        ];

        // ====== [2] CANCELLED — Dibatalkan manual (3 data) ======
        // Reservasi yang dibatalkan oleh pengguna sebelum atau saat pelaksanaan.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(10)->setTime(14, 0, 0),
            'end_time' => $now->copy()->subDays(10)->setTime(16, 0, 0),
            'purpose' => 'Diskusi mendadak dibatalkan karena prioritas berubah',
            'visitor_count' => 5,
            'status' => 'cancelled',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(6)->setTime(10, 0, 0),
            'end_time' => $now->copy()->subDays(6)->setTime(12, 0, 0),
            'purpose' => 'Acara dibatalkan karena peserta tidak memenuhi kuota',
            'visitor_count' => 3,
            'status' => 'cancelled',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(3)->setTime(15, 0, 0),
            'end_time' => $now->copy()->subDays(3)->setTime(17, 0, 0),
            'purpose' => 'Rapat direschedule ke minggu depan',
            'visitor_count' => 7,
            'status' => 'cancelled',
        ];

        // ====== [3] REJECTED — Ditolak admin (3 data) ======
        // Reservasi yang ditolak admin karena alasan tertentu.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(18)->setTime(11, 0, 0),
            'end_time' => $now->copy()->subDays(18)->setTime(13, 0, 0),
            'purpose' => 'Pengajuan ditolak - kapasitas ruangan kurang dari jumlah peserta',
            'visitor_count' => 35,
            'status' => 'rejected',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(9)->setTime(14, 0, 0),
            'end_time' => $now->copy()->subDays(9)->setTime(16, 0, 0),
            'purpose' => 'Ditolak - bersamaan dengan reservasi lain yang sudah disetujui',
            'visitor_count' => 4,
            'status' => 'rejected',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(4)->setTime(8, 0, 0),
            'end_time' => $now->copy()->subDays(4)->setTime(10, 0, 0),
            'purpose' => 'Ditolak - ruangan sedang dalam pemeliharaan',
            'visitor_count' => 6,
            'status' => 'rejected',
        ];

        // ====== [4] APPROVED (PAST) — 🔴 TARGET CRON JOB: harus jadi `completed` ======
        // Status `approved`, tapi end_time sudah LEWAT → cron/autoTransition()
        // harus mengubahnya menjadi `completed` secara otomatis.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subHours(8)->setMinutes(0)->setSeconds(0),
            'end_time' => $now->copy()->subHours(6)->setMinutes(0)->setSeconds(0),
            'purpose' => '[CRON TEST] Seminar product update — sudah berakhir 6 jam lalu',
            'visitor_count' => 15,
            'status' => 'approved', // → harusnya jadi `completed` setelah cron
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(2)->setTime(9, 0, 0),
            'end_time' => $now->copy()->subDays(2)->setTime(11, 0, 0),
            'purpose' => '[CRON TEST] Rapat evaluasi kinerja Q4 — berakhir 2 hari lalu',
            'visitor_count' => 9,
            'status' => 'approved', // → harusnya jadi `completed` setelah cron
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(5)->setTime(13, 0, 0),
            'end_time' => $now->copy()->subDays(5)->setTime(15, 0, 0),
            'purpose' => '[CRON TEST] Diskusi strategi bisnis 2026 — berakhir 5 hari lalu',
            'visitor_count' => 11,
            'status' => 'approved', // → harusnya jadi `completed` setelah cron
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subHours(3)->setMinutes(0)->setSeconds(0),
            'end_time' => $now->copy()->subHours(1)->setMinutes(0)->setSeconds(0),
            'purpose' => '[CRON TEST] Briefing tim sales — berakhir 1 jam lalu',
            'visitor_count' => 7,
            'status' => 'approved', // → harusnya jadi `completed` setelah cron
        ];

        // ====== [5] PENDING (PAST) — 🔴 TARGET CRON JOB: harus jadi `cancelled` ======
        // Status `pending` (belum disetujui), tapi start_time sudah LEWAT →
        // cron/autoTransition() harus mengubahnya menjadi `cancelled` secara otomatis.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subHours(4)->setMinutes(0)->setSeconds(0),
            'end_time' => $now->copy()->subHours(2)->setMinutes(0)->setSeconds(0),
            'purpose' => '[CRON TEST] Rapat divisi keuangan — start sudah lewat, belum disetujui',
            'visitor_count' => 8,
            'status' => 'pending', // → harusnya jadi `cancelled` setelah cron
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(1)->setTime(10, 0, 0),
            'end_time' => $now->copy()->subDays(1)->setTime(12, 0, 0),
            'purpose' => '[CRON TEST] Sesi orientasi karyawan baru — kemarin, belum diapprove',
            'visitor_count' => 5,
            'status' => 'pending', // → harusnya jadi `cancelled` setelah cron
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subDays(3)->setTime(14, 0, 0),
            'end_time' => $now->copy()->subDays(3)->setTime(16, 0, 0),
            'purpose' => '[CRON TEST] Workshop design thinking — 3 hari lalu, pending terus',
            'visitor_count' => 12,
            'status' => 'pending', // → harusnya jadi `cancelled` setelah cron
        ];

        // ====== [6] APPROVED (ONGOING) — Sedang berlangsung saat ini (2 data) ======
        // Reservasi yang start_time sudah lewat namun end_time belum,
        // artinya sedang berjalan sekarang dan status `approved` adalah benar.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subHours(1)->setMinutes(0)->setSeconds(0),
            'end_time' => $now->copy()->addHours(1)->setMinutes(0)->setSeconds(0),
            'purpose' => 'Rapat koordinasi proyek berjalan — sedang berlangsung',
            'visitor_count' => 9,
            'status' => 'approved', // valid: end_time belum lewat
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->subMinutes(30)->setSeconds(0),
            'end_time' => $now->copy()->addMinutes(90)->setSeconds(0),
            'purpose' => 'Presentasi progress Q1 kepada direksi — sedang berlangsung',
            'visitor_count' => 14,
            'status' => 'approved', // valid: end_time belum lewat
        ];

        // ====== [7] PENDING (FUTURE) — Menunggu persetujuan admin (3 data) ======
        // Reservasi di masa depan yang belum disetujui, status `pending` adalah benar.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(1)->setTime(10, 0, 0),
            'end_time' => $now->copy()->addDays(1)->setTime(12, 0, 0),
            'purpose' => 'Review anggaran bulanan — menunggu persetujuan',
            'visitor_count' => 5,
            'status' => 'pending',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(3)->setTime(14, 0, 0),
            'end_time' => $now->copy()->addDays(3)->setTime(16, 0, 0),
            'purpose' => 'Kickoff proyek sistem ERP baru — pending konfirmasi',
            'visitor_count' => 11,
            'status' => 'pending',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(5)->setTime(9, 0, 0),
            'end_time' => $now->copy()->addDays(5)->setTime(11, 0, 0),
            'purpose' => 'Presentasi vendor solusi cloud — menunggu review',
            'visitor_count' => 8,
            'status' => 'pending',
        ];

        // ====== [8] APPROVED (FUTURE) — Sudah disetujui, belum dilaksanakan (5 data) ======
        // Reservasi masa depan yang sudah di-approve oleh admin.

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(2)->setTime(15, 0, 0),
            'end_time' => $now->copy()->addDays(2)->setTime(17, 0, 0),
            'purpose' => 'Review anggaran Q2 divisi keuangan — sudah disetujui',
            'visitor_count' => 5,
            'status' => 'approved',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(4)->setTime(13, 0, 0),
            'end_time' => $now->copy()->addDays(4)->setTime(15, 30, 0),
            'purpose' => 'Survey kepuasan pelanggan — presentasi hasil',
            'visitor_count' => 20,
            'status' => 'approved',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(6)->setTime(10, 0, 0),
            'end_time' => $now->copy()->addDays(6)->setTime(12, 0, 0),
            'purpose' => 'Pelatihan penggunaan ERP untuk user baru',
            'visitor_count' => 16,
            'status' => 'approved',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(8)->setTime(14, 0, 0),
            'end_time' => $now->copy()->addDays(8)->setTime(16, 0, 0),
            'purpose' => 'Perencanaan roadmap produk 2027',
            'visitor_count' => 12,
            'status' => 'approved',
        ];

        $reservations[] = [
            'user_id' => $nextUser(),
            'room_id' => $nextRoom(),
            'start_time' => $now->copy()->addDays(10)->setTime(9, 0, 0),
            'end_time' => $now->copy()->addDays(10)->setTime(12, 0, 0),
            'purpose' => 'Town hall bulanan seluruh karyawan',
            'visitor_count' => 45,
            'status' => 'approved',
        ];

        // ====== Insert all reservations ======
        $reservationCount = 1;
        foreach ($reservations as $reservation) {
            $date = Carbon::parse($reservation['start_time'])->format('Ymd');
            $id = sprintf('RSV-%s-%02d', $date, $reservationCount);

            DB::table('t_reservations')->insert([
                'id' => $id,
                'user_id' => $reservation['user_id'],
                'room_id' => $reservation['room_id'],
                'start_time' => $reservation['start_time']->setTimezone('UTC'),
                'end_time' => $reservation['end_time']->setTimezone('UTC'),
                'purpose' => $reservation['purpose'],
                'visitor_count' => $reservation['visitor_count'],
                'status' => $reservation['status'],
                'created_at' => $reservation['start_time']->copy()->subDays(rand(1, 7))->setTimezone('UTC'),
                'created_by' => $reservation['user_id'],
                'updated_by' => $reservation['user_id'],
            ]);

            $reservationCount++;
        }

        $this->command->info('Reservations seeded successfully!');
        $this->command->info(count($reservations) . ' reservations created');
        $this->command->info('');
        $this->command->info('Status breakdown:');
        $this->command->info('  [1] completed      : 4 data (historis, status benar)');
        $this->command->info('  [2] cancelled      : 3 data (dibatalkan manual)');
        $this->command->info('  [3] rejected       : 3 data (ditolak admin)');
        $this->command->info('  [4] approved (past): 4 data 🔴 TARGET CRON → harus jadi completed');
        $this->command->info('  [5] pending (past) : 3 data 🔴 TARGET CRON → harus jadi cancelled');
        $this->command->info('  [6] approved (now) : 2 data (sedang berlangsung, valid)');
        $this->command->info('  [7] pending (future): 3 data (menunggu approval, valid)');
        $this->command->info('  [8] approved (future): 5 data (sudah disetujui, valid)');
        $this->command->info('');
        $this->command->warn('Jalankan: php artisan schedule:work  untuk tes cron job otomatis.');
    }
}

