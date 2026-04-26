<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaintenanceReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $rooms,
        private array $summary,
    ) {}

    public function array(): array
    {
        $rows = [];

        $rows[] = ['LAPORAN MAINTENANCE & KERUSAKAN RUANGAN'];
        $rows[] = ['Periode', $this->summary['date_from'] . ' s.d. ' . $this->summary['date_to']];
        $rows[] = ['Total Ruangan', $this->summary['total_rooms']];
        $rows[] = ['Dalam Maintenance', $this->summary['under_maintenance']];
        $rows[] = ['Total Komplain', $this->summary['total_complaints']];
        $rows[] = [];

        $rows[] = ['Ruangan', 'Lantai', 'Kapasitas', 'Status Maintenance', 'Total Komplain', 'Terbuka', 'Dikerjakan', 'Selesai', 'Ditolak'];

        foreach ($this->rooms as $room) {
            $rows[] = [
                $room['name'],
                $room['floor'],
                $room['capacity'],
                $room['is_maintenance'] ? 'Maintenance' : 'Normal',
                $room['total_complaints'],
                $room['open'],
                $room['in_progress'],
                $room['resolved'],
                $room['rejected'],
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
        return 'Maintenance & Kerusakan';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
