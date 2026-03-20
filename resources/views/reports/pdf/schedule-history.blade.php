<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #222;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 2px;
        }

        .subtitle {
            color: #666;
            font-size: 11px;
            margin-bottom: 12px;
        }

        .summary-box {
            background: #f4f4f4;
            border: 1px solid #ddd;
            padding: 8px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
        }

        .summary-box span {
            margin-right: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th {
            background: #343a40;
            color: #fff;
            padding: 5px 8px;
            text-align: left;
            font-size: 10px;
        }

        td {
            padding: 4px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background: #f9f9f9;
        }

        .footer {
            margin-top: 16px;
            font-size: 9px;
            color: #aaa;
            text-align: right;
        }
    </style>
</head>

<body>
    <h1>Laporan Jadwal &amp; Histori Reservasi</h1>
    <div class="subtitle">
        Periode: {{ $summary['date_from'] }} s/d {{ $summary['date_to'] }} &nbsp;|&nbsp;
        Dicetak: {{ now()->format('d M Y H:i') }} WIB
    </div>

    <div class="summary-box">
        <span><strong>Total:</strong> {{ $summary['total'] }}</span>
        <span><strong>Disetujui:</strong> {{ $summary['approved'] }}</span>
        <span><strong>Selesai:</strong> {{ $summary['completed'] }}</span>
        <span><strong>Ditolak:</strong> {{ $summary['rejected'] }}</span>
        <span><strong>Dibatalkan:</strong> {{ $summary['cancelled'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pemohon</th>
                <th>No. Karyawan</th>
                <th>Ruangan</th>
                <th>Lantai</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Pengunjung</th>
                <th>Status</th>
                <th>Tujuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->user?->full_name ?? '-' }}</td>
                    <td>{{ $r->user?->employee_id ?? '-' }}</td>
                    <td>{{ $r->room?->name ?? '-' }}</td>
                    <td>{{ $r->room?->floor ?? '-' }}</td>
                    <td>{{ $r->start_time?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->end_time?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->visitor_count }}</td>
                    <td>{{ strtoupper($r->status) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($r->purpose ?? '-', 40) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;color:#999;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Sistem Reservasi Ruangan Rapat</div>
</body>

</html>
