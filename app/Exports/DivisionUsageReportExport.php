<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DivisionUsageReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $byDivision,
        private array $summary,
    ) {}

    public function array(): array
    {
        $rows = [];

        $rows[] = ['REKAPITULASI PEMAKAIAN RUANGAN PER DIVISI'];
        $rows[] = ['Periode', $this->summary['date_from'] . ' s.d. ' . $this->summary['date_to']];
        $rows[] = ['Total Reservasi', $this->summary['total_reservations']];
        $rows[] = ['Total Jam Pemakaian', $this->summary['total_hours'] . ' jam'];
        $rows[] = ['Total Pengunjung', $this->summary['total_visitors']];
        $rows[] = [];

        $rows[] = ['Divisi', 'Kode', 'Jml Reservasi', 'Total Jam', 'Rata-rata Jam/Reservasi', 'Total Pengunjung', 'Ruangan Dipakai'];

        foreach ($this->byDivision as $row) {
            $rows[] = [
                $row['division_name'],
                $row['division_code'],
                $row['reservation_count'],
                $row['total_hours'],
                $row['avg_hours'],
                $row['total_visitors'],
                implode(', ', $row['rooms_used']),
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
        return 'Pemakaian per Divisi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
