<?php

namespace App\Exports;

use App\Support\ReservationStatus;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserActivityReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $reservations,
        private Collection $byUser,
        private array $summary
    ) {}

    public function title(): string
    {
        return 'Aktivitas Per Pengguna';
    }

    public function headings(): array
    {
        return ['ID', 'No. Karyawan', 'Nama Pengguna', 'Ruangan', 'Tgl Mulai', 'Tgl Selesai', 'Status', 'Snack', 'Makan Siang'];
    }

    public function array(): array
    {
        return $this->reservations->map(fn($r) => [
            $r->id,
            $r->user?->employee_id ?? '-',
            $r->user?->full_name ?? '-',
            $r->room?->name ?? '-',
            $r->start_time?->format('d/m/Y H:i'),
            $r->end_time?->format('d/m/Y H:i'),
            match ($r->status) {
                ReservationStatus::Pending->value   => 'Menunggu',
                ReservationStatus::Approved->value  => 'Disetujui',
                ReservationStatus::Completed->value => 'Selesai',
                ReservationStatus::Rejected->value  => 'Ditolak',
                ReservationStatus::Cancelled->value => 'Dibatalkan',
                default => $r->status,
            },
            $r->with_snack ? 'Ya' : 'Tidak',
            $r->with_lunch ? 'Ya' : 'Tidak',
        ])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
