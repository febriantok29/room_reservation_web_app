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

class PeriodicReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $grouped,
        private array $summary
    ) {}

    public function title(): string
    {
        return 'Ringkasan Periodik';
    }

    public function headings(): array
    {
        $periodLabel = match ($this->summary['period']) {
            'daily'   => 'Tanggal',
            'weekly'  => 'Minggu',
            default   => 'Bulan',
        };
        return [$periodLabel, 'Total', 'Disetujui', 'Selesai', 'Ditolak', 'Dibatalkan', 'Menunggu', 'Total Pengunjung'];
    }

    public function array(): array
    {
        $key = match ($this->summary['period']) {
            'daily'  => 'date',
            'weekly' => 'week',
            default  => 'month',
        };
        return $this->grouped->map(fn($row) => [
            $row[$key],
            $row['total'],
            $row[ReservationStatus::Approved->value],
            $row[ReservationStatus::Completed->value],
            $row[ReservationStatus::Rejected->value],
            $row[ReservationStatus::Cancelled->value],
            $row[ReservationStatus::Pending->value],
            $row['visitors'],
        ])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
