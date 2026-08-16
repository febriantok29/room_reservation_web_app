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

class ScheduleHistoryReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $reservations,
        private array $summary
    ) {}

    public function title(): string
    {
        return 'Jadwal & Histori';
    }

    public function headings(): array
    {
        return ['ID', 'Pemohon', 'No. Karyawan', 'Ruangan', 'Lantai', 'Tgl Mulai', 'Tgl Selesai', 'Pengunjung', 'Status', 'Tujuan'];
    }

    public function array(): array
    {
        return $this->reservations->map(fn($r) => [
            $r->id,
            $r->user?->full_name ?? '-',
            $r->user?->employee_id ?? '-',
            $r->room?->name ?? '-',
            $r->room?->floor ?? '-',
            $r->start_time_short,
            $r->end_time_short,
            $r->visitor_count,
            match ($r->status) {
                ReservationStatus::Pending->value   => 'Menunggu',
                ReservationStatus::Approved->value  => 'Disetujui',
                ReservationStatus::Completed->value => 'Selesai',
                ReservationStatus::Rejected->value  => 'Ditolak',
                ReservationStatus::Cancelled->value => 'Dibatalkan',
                default => $r->status,
            },
            $r->purpose ?? '-',
        ])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
