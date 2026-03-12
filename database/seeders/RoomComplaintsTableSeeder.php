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
        // Require completed reservations and user/room data
        $completedReservations = DB::table('t_reservations')
            ->where('status', 'completed')
            ->get();

        $facilities = DB::table('m_facilities')->get();

        if ($completedReservations->isEmpty()) {
            $this->command->warn('No completed reservations found. RoomComplaints seeder skipped.');
            return;
        }

        $now = Carbon::now();

        $complaints = [
            // Complaint 1: open (no action taken yet)
            [
                'reservation'        => $completedReservations[0] ?? null,
                'facility'           => $facilities[0] ?? null,
                'title'              => 'Proyektor tidak dapat menyala',
                'description'        => "Saat rapat berlangsung, proyektor di ruangan ini tidak dapat dinyalakan sama sekali. "
                    . "Sudah dicoba menekan tombol power beberapa kali namun tidak ada respons. "
                    . "Kondisi ini menghambat jalannya presentasi.",
                'status'             => 'open',
                'resolution_notes'   => null,
                'resolved_at_offset' => null,
            ],
            // Complaint 2: in_progress
            [
                'reservation'        => $completedReservations[1] ?? $completedReservations[0],
                'facility'           => $facilities[1] ?? null,
                'title'              => 'AC ruangan tidak dingin',
                'description'        => "AC menyala namun tidak mengeluarkan udara dingin. "
                    . "Suhu ruangan tetap panas selama sesi berlangsung, membuat peserta tidak nyaman.",
                'status'             => 'in_progress',
                'resolution_notes'   => null,
                'resolved_at_offset' => null,
            ],
            // Complaint 3: resolved
            [
                'reservation'        => $completedReservations[2] ?? $completedReservations[0],
                'facility'           => null,
                'title'              => 'Kursi rusak dan kaki patah',
                'description'        => "Terdapat satu kursi di sudut ruangan dengan kaki yang patah. "
                    . "Hampir mencelakakan salah satu peserta rapat. "
                    . "Mohon segera diganti untuk keselamatan pengguna berikutnya.",
                'status'             => 'resolved',
                'resolution_notes'   => "Kursi yang rusak telah diganti dengan unit baru. "
                    . "Pengecekan berkala terhadap kondisi furnitur telah dijadwalkan setiap bulan.",
                'resolved_at_offset' => -3, // days ago
            ],
            // Complaint 4: rejected
            [
                'reservation'        => $completedReservations[3] ?? $completedReservations[0],
                'facility'           => $facilities[0] ?? null,
                'title'              => 'Layar proyektor kotor',
                'description'        => "Layar proyektor tampak ada noda dan kotor, mengurangi kualitas tampilan presentasi.",
                'status'             => 'rejected',
                'resolution_notes'   => "Setelah dicek, kondisi layar proyektor dalam keadaan baik dan bersih. "
                    . "Diduga noda yang terlihat disebabkan oleh pencahayaan ruangan saat itu. "
                    . "Tidak diperlukan tindakan perbaikan.",
                'resolved_at_offset' => -7, // days ago
            ],
        ];

        foreach ($complaints as $index => $data) {
            if (!$data['reservation']) {
                continue;
            }

            $reservation = $data['reservation'];
            $createdAt = $now->copy()->subDays(10 - $index * 2);

            $resolvedAt = null;
            if ($data['resolved_at_offset'] !== null) {
                $resolvedAt = $now->copy()->addDays($data['resolved_at_offset']);
            }

            // Generate ID manually (seeder bypasses the model boot)
            $formattedDate = $createdAt->format('Ymd');
            $sequence = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $id = "CMP-{$formattedDate}-{$sequence}";

            DB::table('t_room_complaints')->insertOrIgnore([
                'id'               => $id,
                'reservation_id'   => $reservation->id,
                'reported_by'      => $reservation->user_id,
                'room_id'          => $reservation->room_id,
                'facility_id'      => $data['facility']?->id,
                'title'            => $data['title'],
                'description'      => $data['description'],
                'photo_path'       => null,
                'status'           => $data['status'],
                'resolution_notes' => $data['resolution_notes'],
                'resolved_at'      => $resolvedAt,
                'resolved_by'      => $data['resolved_at_offset'] !== null ? $reservation->user_id : null,
                'created_at'       => $createdAt,
                'created_by'       => $reservation->user_id,
                'updated_at'       => $createdAt,
                'updated_by'       => $reservation->user_id,
                'deleted_at'       => null,
                'deleted_by'       => null,
            ]);
        }

        $this->command->info('RoomComplaints seeded: ' . count($complaints) . ' records.');
    }
}
