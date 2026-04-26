<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoomComplaintsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get completed reservations and facilities
        $completedReservations = DB::table('t_reservations')
            ->where('status', 'completed')
            ->get();

        $facilities = DB::table('m_facilities')->get();
        $rooms = DB::table('m_rooms')->get();

        if ($completedReservations->isEmpty()) {
            $this->command->warn('No completed reservations found. RoomComplaints seeder skipped.');
            return;
        }

        $now = Carbon::now();
        $complaints = [];

        // Complaint templates for different facilities
        $projectorIssues = [
            ['title' => 'Proyektor tidak dapat menyala', 'desc' => 'Proyektor tidak merespons saat tombol power ditekan. Lampu indikator tidak menyala sama sekali.'],
            ['title' => 'Gambar proyektor buram', 'desc' => 'Proyektor menyala namun hasil proyeksi sangat buram dan tidak dapat difokuskan dengan baik.'],
            ['title' => 'Kabel HDMI proyektor rusak', 'desc' => 'Kabel HDMI untuk koneksi laptop ke proyektor putus di bagian ujung konektor.'],
            ['title' => 'Remote proyektor hilang', 'desc' => 'Remote control proyektor tidak ditemukan di dalam ruangan, menyulitkan pengaturan.'],
            ['title' => 'Proyektor berbunyi bising', 'desc' => 'Proyektor mengeluarkan bunyi kipas yang sangat keras dan mengganggu konsentrasi peserta.'],
        ];

        $acIssues = [
            ['title' => 'AC tidak dingin', 'desc' => 'AC menyala namun tidak mengeluarkan udara dingin. Suhu ruangan tetap panas dan tidak nyaman.'],
            ['title' => 'AC bocor menetes', 'desc' => 'AC mengeluarkan tetesan air ke lantai ruangan. Membentuk genangan di bawah unit AC.'],
            ['title' => 'AC berbunyi berisik', 'desc' => 'AC mengeluarkan bunyi berisik seperti ada bagian yang lepas atau bergetar.'],
            ['title' => 'Remote AC tidak berfungsi', 'desc' => 'Remote control AC tidak dapat mengatur suhu atau mode. Sudah dicoba ganti baterai namun tetap tidak bekerja.'],
            ['title' => 'AC mati tiba-tiba', 'desc' => 'AC sering mati sendiri secara tiba-tiba di tengah penggunaan tanpa sebab yang jelas.'],
        ];

        $whiteboardIssues = [
            ['title' => 'Spidol whiteboard habis', 'desc' => 'Semua spidol whiteboard di ruangan sudah habis atau kering. Tidak dapat digunakan untuk presentasi.'],
            ['title' => 'Papan tulis kotor susah dihapus', 'desc' => 'Permukaan whiteboard kotor dan bekas spidol lama susah dihapus. Mengganggu penulisan baru.'],
            ['title' => 'Penghapus whiteboard hilang', 'desc' => 'Penghapus whiteboard tidak tersedia di ruangan. Hanya ada spidol tanpa alat pembersih.'],
            ['title' => 'Permukaan whiteboard rusak', 'desc' => 'Permukaan whiteboard tergores dan tidak rata. Sulit untuk menulis dengan jelas.'],
        ];

        $furnitureIssues = [
            ['title' => 'Kursi rusak kaki patah', 'desc' => 'Salah satu kursi memiliki kaki yang patah. Sangat berbahaya dan hampir mencelakakan peserta.'],
            ['title' => 'Meja goyang tidak stabil', 'desc' => 'Meja ruangan goyang dan tidak stabil saat digunakan. Mengganggu kenyamanan menulis.'],
            ['title' => 'Kursi roda macet', 'desc' => 'Roda pada kursi kantor macet dan sulit digerakkan. Beberapa kursi tidak dapat berputar.'],
            ['title' => 'Sandaran kursi lepas', 'desc' => 'Sandaran pada beberapa kursi lepas dari dudukannya. Tidak aman untuk digunakan.'],
        ];

        $generalIssues = [
            ['title' => 'Lampu ruangan mati', 'desc' => 'Beberapa lampu di ruangan mati dan membuat ruangan kurang terang untuk rapat.'],
            ['title' => 'Colokan listrik tidak berfungsi', 'desc' => 'Colokan listrik di dinding tidak mengeluarkan arus. Tidak dapat mengisi daya laptop.'],
            ['title' => 'Pintu sulit ditutup', 'desc' => 'Pintu ruangan sulit ditutup dengan rapat. Engsel pintu tampak rusak atau kendur.'],
            ['title' => 'Kebersihan ruangan kurang', 'desc' => 'Ruangan terlihat kotor dengan sampah berserakan. Lantai tidak disapu dengan bersih.'],
            ['title' => 'Bau tidak sedap dalam ruangan', 'desc' => 'Ruangan berbau tidak sedap seperti pengap atau lembab. Sangat mengganggu kenyamanan.'],
            ['title' => 'Jendela tidak dapat dibuka', 'desc' => 'Jendela ruangan macet dan tidak dapat dibuka untuk ventilasi udara.'],
        ];

        $resolutionNotes = [
            'Telah diperbaiki oleh tim maintenance. Komponen yang rusak sudah diganti dengan yang baru.',
            'Sudah dilakukan pengecekan dan cleaning menyeluruh oleh teknisi.',
            'Unit telah diganti dengan yang baru. Kondisi peralatan sudah dipastikan berfungsi normal.',
            'Perbaikan sementara sudah dilakukan. Penjadwalan penggantian unit baru dalam proses.',
            'Sudah dibersihkan dan dilakukan penyetelan ulang. Kondisi kembali normal.',
            'Tim cleaning sudah melakukan pembersihan menyeluruh dan pemeriksaan berkala dijadwalkan.',
            'Spare part sudah dipesan dan akan segera dipasang setelah barang tiba.',
            'Perbaikan selesai dan unit sudah diuji coba dengan baik. Tidak ada masalah ditemukan.',
        ];

        $rejectionNotes = [
            'Setelah dicek, kondisi peralatan dalam keadaan normal. Tidak ditemukan kerusakan.',
            'Diduga masalah disebabkan oleh faktor eksternal dan bukan kerusakan perangkat.',
            'Kondisi yang dilaporkan adalah bagian dari karakteristik normal perangkat.',
            'Tidak dapat direproduksi saat pengecekan. Kemungkinan masalah sudah teratasi sendiri.',
        ];

        // ========================================
        // Generate 60 complaints with varied statuses (3:1 ratio with 180 reservations)
        // ========================================
        $complaintIndex = 0;
        $facilityIndex = 0;
        $reservationIndex = 0;

        $nextFacility = function () use (&$facilityIndex, $facilities) {
            if ($facilities->isEmpty()) return null;
            $facility = $facilities[$facilityIndex % count($facilities)];
            $facilityIndex++;
            return $facility;
        };

        $nextReservation = function () use (&$reservationIndex, $completedReservations) {
            $reservation = $completedReservations[$reservationIndex % count($completedReservations)];
            $reservationIndex++;
            return $reservation;
        };

        // Cycle through all issue templates for more variety
        $allIssues = array_merge($projectorIssues, $acIssues, $whiteboardIssues, $furnitureIssues, $generalIssues);

        // 16 OPEN complaints (no action yet)
        for ($i = 0; $i < 16; $i++) {
            $issue = $allIssues[$i % count($allIssues)];
            $reservation = $nextReservation();
            $facility = rand(0, 1) ? $nextFacility() : null;

            $complaints[] = [
                'reservation' => $reservation,
                'facility' => $facility,
                'title' => $issue['title'],
                'description' => $issue['desc'],
                'status' => 'open',
                'resolution_notes' => null,
                'resolved_at_offset' => null,
                'created_days_ago' => rand(1, 15),
            ];
        }

        // 20 IN_PROGRESS complaints (being worked on)
        for ($i = 0; $i < 20; $i++) {
            $issue = $allIssues[($i + 16) % count($allIssues)];
            $reservation = $nextReservation();
            $facility = rand(0, 1) ? $nextFacility() : null;

            $complaints[] = [
                'reservation' => $reservation,
                'facility' => $facility,
                'title' => $issue['title'],
                'description' => $issue['desc'],
                'status' => 'in_progress',
                'resolution_notes' => null,
                'resolved_at_offset' => null,
                'created_days_ago' => rand(5, 30),
            ];
        }

        // 20 RESOLVED complaints
        for ($i = 0; $i < 20; $i++) {
            $issue = $allIssues[($i + 36) % count($allIssues)];
            $reservation = $nextReservation();
            $facility = rand(0, 1) ? $nextFacility() : null;

            $complaints[] = [
                'reservation' => $reservation,
                'facility' => $facility,
                'title' => $issue['title'],
                'description' => $issue['desc'],
                'status' => 'resolved',
                'resolution_notes' => $resolutionNotes[array_rand($resolutionNotes)],
                'resolved_at_offset' => rand(-60, -3),
                'created_days_ago' => rand(25, 120),
            ];
        }

        // 4 REJECTED complaints
        for ($i = 0; $i < 4; $i++) {
            $issue = $allIssues[($i + 56) % count($allIssues)];
            $reservation = $nextReservation();
            $facility = rand(0, 1) ? $nextFacility() : null;

            $complaints[] = [
                'reservation' => $reservation,
                'facility' => $facility,
                'title' => $issue['title'],
                'description' => $issue['desc'],
                'status' => 'rejected',
                'resolution_notes' => $rejectionNotes[array_rand($rejectionNotes)],
                'resolved_at_offset' => rand(-45, -5),
                'created_days_ago' => rand(20, 90),
            ];
        }

        // Insert all complaints
        foreach ($complaints as $index => $data) {
            $createdAt = $now->copy()->subDays($data['created_days_ago']);
            $resolvedAt = null;

            if ($data['resolved_at_offset'] !== null) {
                $resolvedAt = $now->copy()->addDays($data['resolved_at_offset']);
            }

            $formattedDate = $createdAt->format('Ymd');
            $sequence = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $id = "CMP-{$formattedDate}-{$sequence}";

            DB::table('t_room_complaints')->insertOrIgnore([
                'id' => $id,
                'reservation_id' => $data['reservation']->id,
                'reported_by' => $data['reservation']->user_id,
                'room_id' => $data['reservation']->room_id,
                'facility_id' => $data['facility']?->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'photo_path' => null,
                'status' => $data['status'],
                'resolution_notes' => $data['resolution_notes'],
                'resolved_at' => $resolvedAt,
                'resolved_by' => $data['resolved_at_offset'] !== null ? $data['reservation']->user_id : null,
                'created_at' => $createdAt,
                'created_by' => $data['reservation']->user_id,
                'updated_at' => $createdAt,
                'updated_by' => $data['reservation']->user_id,
                'deleted_at' => null,
                'deleted_by' => null,
            ]);
        }

        $this->command->info('RoomComplaints seeded: ' . count($complaints) . ' total (3:1 ratio with 180 reservations)');
        $this->command->info('  - 16 open (awaiting action)');
        $this->command->info('  - 20 in_progress (being worked on)');
        $this->command->info('  - 20 resolved (fixed and closed)');
        $this->command->info('  - 4 rejected (not actionable)');
    }
}
