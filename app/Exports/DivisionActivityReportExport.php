<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DivisionActivityReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $reservations,
        private Collection $byDivision,
        private array $summary,
    ) {}

    public function array(): array
    {
        $rows = [];

        // Header info
        $rows[] = ['LAPORAN AKTIVITAS PER DIVISI'];
        $rows[] = ['Periode', $this->summary['date_from'] . ' s.d. ' . $this->summary['date_to']];
        $rows[] = ['Total Reservasi', $this->summary['total_reservations']];
        $rows[] = [];

        // Summary per division
        $rows[] = ['=== RINGKASAN PER DIVISI ==='];
        $rows[] = ['Divisi', 'Kode', 'Total', 'Disetujui', 'Selesai', 'Ditolak', 'Dibatalkan', 'Menunggu', 'Pengunjung'];

        foreach ($this->byDivision as $row) {
            $rows[] = [
                $row['division_name'],
                $row['division_code'],
                $row['total'],
                $row['approved'],
                $row['completed'],
                $row['rejected'],
                $row['cancelled'],
                $row['pending'],
                $row['visitors'],
            ];
        }

        $rows[] = [];
        $rows[] = ['=== DETAIL RESERVASI ==='];
        $rows[] = ['ID Reservasi', 'Pemohon', 'No. Karyawan', 'Divisi', 'Ruangan', 'Tgl Mulai', 'Tgl Selesai', 'Status', 'Pengunjung'];

        foreach ($this->reservations as $r) {
            $rows[] = [
                $r->id,
                $r->user?->full_name ?? '-',
                $r->user?->employee_id ?? '-',
                $r->user?->division?->name ?? 'Admin / Tanpa Divisi',
                $r->room?->name ?? '-',
                $r->start_time?->format('d/m/Y H:i'),
                $r->end_time?->format('d/m/Y H:i'),
                $r->status,
                $r->visitor_count,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Aktivitas Per Divisi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
