<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
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
            font-size: 11px;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        td:first-child {
            text-align: left;
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
    <h1>Laporan Ringkasan Periodik Reservasi</h1>
    <div class="subtitle">
        Periode: {{ ucfirst($period) }} &nbsp;|&nbsp;
        Tahun: {{ $year }}
        @if ($period === 'daily')
            &nbsp;|&nbsp; Bulan: {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
        @endif
        &nbsp;|&nbsp; Dicetak: {{ now()->format('d M Y H:i') }} WIB
    </div>

    <div class="summary-box">
        <span><strong>Total:</strong> {{ $summary['total'] }}</span>
        <span><strong>Selesai:</strong> {{ $summary['completed'] }}</span>
        <span><strong>Disetujui:</strong> {{ $summary['approved'] }}</span>
        <span><strong>Ditolak:</strong> {{ $summary['rejected'] }}</span>
        <span><strong>Dibatalkan:</strong> {{ $summary['cancelled'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ $period === 'daily' ? 'Tanggal' : ($period === 'weekly' ? 'Minggu' : 'Bulan') }}</th>
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
            @php $key = $period === 'daily' ? 'date' : ($period === 'weekly' ? 'week' : 'month'); @endphp
            @forelse($grouped as $row)
                <tr>
                    <td>{{ $row[$key] }}</td>
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
                    <td colspan="8" style="text-align:center;color:#999;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Sistem Reservasi Ruangan Rapat</div>
</body>

</html>
