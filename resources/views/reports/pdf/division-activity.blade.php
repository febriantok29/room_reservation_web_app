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
        }

        tr:nth-child(even) td {
            background: #f9f9f9;
        }

        .section-header {
            font-weight: bold;
            background: #e9ecef;
            padding: 5px 8px;
            margin: 14px 0 4px;
            font-size: 11px;
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
    <h1>Laporan Aktivitas Reservasi per Divisi</h1>
    <div class="subtitle">
        Periode: {{ $summary['date_from'] }} s/d {{ $summary['date_to'] }} &nbsp;|&nbsp;
        Dicetak: {{ now()->format('d M Y H:i') }} WIB
    </div>

    <div class="summary-box">
        <span><strong>Total Reservasi:</strong> {{ $summary['total_reservations'] }}</span>
        <span><strong>Total Divisi:</strong> {{ $byDivision->count() }}</span>
        <span><strong>Total Pengunjung:</strong> {{ $summary['total_visitors'] }}</span>
    </div>

    {{-- Summary per division --}}
    <div class="section-header">Ringkasan per Divisi</div>
    <table>
        <thead>
            <tr>
                <th>Divisi</th>
                <th>Kode</th>
                <th>Total</th>
                <th>Disetujui</th>
                <th>Selesai</th>
                <th>Ditolak</th>
                <th>Dibatalkan</th>
                <th>Menunggu</th>
                <th>Pengunjung</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($byDivision as $row)
                <tr>
                    <td>{{ $row['division_name'] }}</td>
                    <td>{{ $row['division_code'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['approved'] }}</td>
                    <td>{{ $row['completed'] }}</td>
                    <td>{{ $row['rejected'] }}</td>
                    <td>{{ $row['cancelled'] }}</td>
                    <td>{{ $row['pending'] }}</td>
                    <td>{{ $row['visitors'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:#999;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detail --}}
    <div class="section-header" style="margin-top:18px;">Detail Reservasi</div>
    <table>
        <thead>
            <tr>
                <th>ID Reservasi</th>
                <th>Pemohon</th>
                <th>No. Karyawan</th>
                <th>Divisi</th>
                <th>Ruangan</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
                <th>Pengunjung</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reservations as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->user?->full_name ?? '-' }}</td>
                    <td>{{ $r->user?->employee_id ?? '-' }}</td>
                    <td>{{ $r->user?->division?->name ?? 'Admin / Tanpa Divisi' }}</td>
                    <td>{{ $r->room?->name ?? '-' }}</td>
                    <td>{{ $r->start_time?->format('d/m/Y H:i') }}</td>
                    <td>{{ $r->end_time?->format('d/m/Y H:i') }}</td>
                    <td>{{ strtoupper($r->status) }}</td>
                    <td>{{ $r->visitor_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:#999;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Sistem Reservasi Ruangan Rapat</div>
</body>

</html>
