<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComplaintReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        private Collection $complaints,
        private array $summary
    ) {}

    public function title(): string
    {
        return 'Laporan Komplain';
    }

    public function headings(): array
    {
        return ['ID', 'Ruangan', 'Pelapor', 'Fasilitas', 'Judul', 'Status', 'Tanggal Lapor', 'Catatan Resolusi'];
    }

    public function array(): array
    {
        return $this->complaints->map(fn($c) => [
            $c->id,
            $c->room?->name ?? '-',
            $c->reporter?->full_name ?? '-',
            $c->facility?->name ?? '-',
            $c->title,
            match ($c->status) {
                'open'        => 'Terbuka',
                'in_progress' => 'Dalam Proses',
                'resolved'    => 'Diselesaikan',
                'rejected'    => 'Ditolak',
                default       => $c->status,
            },
            $c->created_at?->format('d/m/Y H:i'),
            $c->resolution_notes ?? '-',
        ])->toArray();
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
