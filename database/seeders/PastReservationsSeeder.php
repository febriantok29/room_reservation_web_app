<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Services\ReservationIdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed reservasi MASA LALU untuk test validasi & alur status.
 *
 * Skenario yang disiapkan:
 *   A. Pending yg sudah mulai (end_time <= now)  -> target auto-cancel (ReservationService::autoTransition)
 *   B. Approved yg sudah selesai (end_time <= now)-> target auto-complete (autoTransition)
 *   C. Overlap di ruangan & waktu yang sama       -> membuat CSP reject reservasi baru yg bentrok
 *   D. Tumpukan "today" (approved, ongoing)       -> test tampilan calendar & detail
 *
 * Idempotent: menghapus dulu semua reservasi yang dibuat seeder ini (purpose ber-prefix [SEEDER]).
 *
 * Prasyarat: user + room sudah di-seed. Jalankan:
 *   php artisan migrate:fresh --seed
 *   php artisan db:seed --class=PastReservationsSeeder
 */
class PastReservationsSeeder extends Seeder
{
    private const PREFIX = '[SEEDER]';

    public function run(): void
    {
        $users = \App\Models\User::query()->where('is_admin', false)->get();
        $rooms = \App\Models\Room::query()->get();

        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Seeder membutuhkan minimal 1 karyawan dan 1 ruangan. Jalankan migrate:fresh --seed (atau seeder user + room) dulu.');
            return;
        }

        // Bersihkan data seeder sebelumnya agar idempotent.
        Reservation::query()->where('purpose', 'like', self::PREFIX.'%')->forceDelete();

        $now = now();
        $u = fn (int $i) => $users[$i % $users->count()]->id;
        $r = fn (int $i) => $rooms[$i % $rooms->count()]->id;

        $reservations = [];

        // [A] Pending yang sudah lewat (auto-cancel oleh cron) — 5
        for ($i = 0; $i < 5; $i++) {
            $start = $now->copy()->subDays($i + 1)->setTime(9, 0, 0);
            $reservations[] = [
                'user_id' => $u($i),
                'room_id' => $r($i),
                'start_time' => $start,
                'end_time' => $start->copy()->addHours(2),
                'purpose' => self::PREFIX.' Rapat pending yang terlewat (target auto-cancel #'.$i.')',
                'visitor_count' => 5 + $i,
                'status' => 'pending',
                'with_snack' => false,
                'with_lunch' => false,
            ];
        }

        // [B] Approved yang sudah selesai (auto-complete oleh cron) — 5
        for ($i = 0; $i < 5; $i++) {
            $start = $now->copy()->subDays($i + 1)->setTime(13, 0, 0);
            $reservations[] = [
                'user_id' => $u($i + 5),
                'room_id' => $r($i + 5),
                'start_time' => $start,
                'end_time' => $start->copy()->addHours(3),
                'purpose' => self::PREFIX.' Rapat approved yang sudah selesai (target auto-complete #'.$i.')',
                'visitor_count' => 8 + $i,
                'status' => 'approved',
                'with_snack' => $i % 2 === 0,
                'with_lunch' => $i % 3 === 0,
            ];
        }

        // [C] Overlap: 3 reservasi di ruangan SAMA & waktu SALING TINDIH (2 hari lalu) — CSP harus reject yg baru
        $overlapRoom = $rooms->first()->id;
        $overlapUser = $users->first()->id;
        $day = $now->copy()->subDays(2);
        $overlaps = [
            [9, 12],
            [10, 13],   // tumpang tindih dengan #1
            [11, 14],   // tumpang tindih dengan #1 & #2
        ];
        foreach ($overlaps as $i => [$h1, $h2]) {
            $reservations[] = [
                'user_id' => $overlapUser,
                'room_id' => $overlapRoom,
                'start_time' => $day->copy()->setTime($h1, 0, 0),
                'end_time' => $day->copy()->setTime($h2, 0, 0),
                'purpose' => self::PREFIX." Overlap room {$rooms->first()->name} slot-".($i + 1)." ({$h1}:00-{$h2}:00)",
                'visitor_count' => 6,
                'status' => 'approved',
                'with_snack' => false,
                'with_lunch' => false,
            ];
        }

        // [D] Hari ini — beberapa status berbeda untuk uji tampilan
        $today = [
            // approved, sedang berlangsung (start <= now < end)
            ['user_id' => $u(1), 'room_id' => $r(1), 'start' => $now->copy()->subHour(), 'end' => $now->copy()->addHour(), 'status' => 'approved', 'visitors' => 7, 'purpose' => self::PREFIX.' Rapat berlangsung sekarang'],
            // pending, nanti hari ini
            ['user_id' => $u(2), 'room_id' => $r(2), 'start' => $now->copy()->addHours(3), 'end' => $now->copy()->addHours(5), 'status' => 'pending', 'visitors' => 4, 'purpose' => self::PREFIX.' Rapat menunggu approval hari ini'],
        ];
        foreach ($today as $t) {
            $reservations[] = [
                'user_id' => $t['user_id'],
                'room_id' => $t['room_id'],
                'start_time' => $t['start'],
                'end_time' => $t['end'],
                'purpose' => $t['purpose'],
                'visitor_count' => $t['visitors'],
                'status' => $t['status'],
                'with_snack' => true,
                'with_lunch' => false,
            ];
        }

        foreach ($reservations as $data) {
            $id = ReservationIdGenerator::generate($data['start_time']->format('Y-m-d'));
            Reservation::query()->create([
                'id' => $id,
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'purpose' => $data['purpose'],
                'visitor_count' => $data['visitor_count'],
                'with_snack' => $data['with_snack'],
                'with_lunch' => $data['with_lunch'],
                'status' => $data['status'],
                'created_by' => $data['user_id'],
                'updated_by' => $data['user_id'],
            ]);
        }

        $this->command->info('Reservasi masa lalu dibuat: '.count($reservations).' (idempotent).');
        $this->command->info('  A. 5 pending lewat   -> jalankan scheduler utk auto-cancel');
        $this->command->info('  B. 5 approved selesai-> jalankan scheduler utk auto-complete');
        $this->command->info('  C. 3 overlap ruangan sama (kemarin) -> tes CSP tolak reservasi bentrok');
        $this->command->info('  D. 2 reservasi hari ini (approved berlangsung + pending)');
        $this->command->info('');
        $this->command->warn('Uji auto-transition: php artisan schedule:work  (atau php artisan schedule:run sekali)');
    }
}
