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

class UsageReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $reservations,
        private Collection $byRoom,
        private array $summary
    ) {}

    public function title(): string
    {
        return 'Rekap Penggunaan';
    }

    public function headings(): array
    {
        return ['ID', 'Ruangan', 'Lantai', 'Pemohon', 'Tgl Mulai', 'Tgl Selesai', 'Durasi (mnt)', 'Pengunjung', 'Status'];
    }

    public function array(): array
    {
        return $this->reservations->map(fn($r) => [
            $r->id,
            $r->room?->name ?? '-',
            $r->room?->floor ?? '-',
            $r->user?->full_name ?? '-',
            $r->start_time_short,
            $r->end_time_short,
            $r->start_time && $r->end_time ? $r->start_time->diffInMinutes($r->end_time) : 0,
            $r->visitor_count,
            match ($r->status) {
                ReservationStatus::Approved->value  => 'Disetujui',
                ReservationStatus::Completed->value => 'Selesai',
                default => $r->status,
            },
        ])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
