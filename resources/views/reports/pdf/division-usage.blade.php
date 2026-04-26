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

        .section-header {
            font-weight: bold;
            background: #e9ecef;
            padding: 5px 8px;
            margin: 14px 0 4px;
            font-size: 11px;
        }

        .rooms-list {
            font-size: 9px;
            color: #555;
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
    <h1>Rekapitulasi Pemakaian Ruangan per Divisi</h1>
    <div class="subtitle">
        Periode: {{ $summary['date_from'] }} s/d {{ $summary['date_to'] }} &nbsp;|&nbsp;
        Dicetak: {{ now()->format('d M Y H:i') }} WIB
    </div>

    <div class="summary-box">
        <span><strong>Total Reservasi:</strong> {{ $summary['total_reservations'] }}</span>
        <span><strong>Total Jam Pemakaian:</strong> {{ $summary['total_hours'] }} jam</span>
        <span><strong>Total Pengunjung:</strong> {{ $summary['total_visitors'] }}</span>
        <span><strong>Divisi Aktif:</strong> {{ $byDivision->count() }}</span>
    </div>

    <div class="section-header">Ringkasan per Divisi</div>
    <table>
        <thead>
            <tr>
                <th>Divisi</th>
                <th>Kode</th>
                <th>Jml Reservasi</th>
                <th>Total Jam</th>
                <th>Rata-rata Jam</th>
                <th>Total Pengunjung</th>
                <th>Ruangan yang Dipakai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($byDivision as $row)
                <tr>
                    <td>{{ $row['division_name'] }}</td>
                    <td>{{ $row['division_code'] }}</td>
                    <td>{{ $row['reservation_count'] }}</td>
                    <td>{{ $row['total_hours'] }} jam</td>
                    <td>{{ $row['avg_hours'] }} jam</td>
                    <td>{{ $row['total_visitors'] }}</td>
                    <td class="rooms-list">{{ implode(', ', $row['rooms_used']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#999;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Per-division room breakdown --}}
    @foreach ($byDivision as $row)
        @if (!empty($row['room_breakdown']))
            <div class="section-header" style="margin-top:14px;">
                Detail Ruangan — {{ $row['division_name'] }} ({{ $row['division_code'] }})
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Ruangan</th>
                        <th>Lantai</th>
                        <th>Jml Reservasi</th>
                        <th>Total Jam</th>
                        <th>Total Pengunjung</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($row['room_breakdown'] as $rb)
                        <tr>
                            <td>{{ $rb['room_name'] }}</td>
                            <td>{{ $rb['floor'] }}</td>
                            <td>{{ $rb['count'] }}</td>
                            <td>{{ $rb['hours'] }} jam</td>
                            <td>{{ $rb['visitors'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="footer">Sistem Reservasi Ruangan Rapat</div>
</body>

</html>
