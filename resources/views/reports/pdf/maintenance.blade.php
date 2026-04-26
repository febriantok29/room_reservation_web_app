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

        .badge-maintenance {
            background: #dc3545;
            color: white;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-normal {
            background: #28a745;
            color: white;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
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
    <h1>Laporan Maintenance &amp; Kerusakan Ruangan</h1>
    <div class="subtitle">
        Periode: {{ $summary['date_from'] }} s/d {{ $summary['date_to'] }} &nbsp;|&nbsp;
        Dicetak: {{ now()->format('d M Y H:i') }} WIB
    </div>

    <div class="summary-box">
        <span><strong>Total Ruangan:</strong> {{ $summary['total_rooms'] }}</span>
        <span><strong>Dalam Maintenance:</strong> {{ $summary['under_maintenance'] }}</span>
        <span><strong>Total Komplain:</strong> {{ $summary['total_complaints'] }}</span>
        <span><strong>Terbuka:</strong> {{ $summary['open_complaints'] }}</span>
        <span><strong>Selesai:</strong> {{ $summary['resolved_complaints'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ruangan</th>
                <th>Lantai</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>Total Komplain</th>
                <th>Terbuka</th>
                <th>Dikerjakan</th>
                <th>Selesai</th>
                <th>Ditolak</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rooms as $room)
                <tr>
                    <td>{{ $room['name'] }}</td>
                    <td>{{ $room['floor'] }}</td>
                    <td>{{ $room['capacity'] }}</td>
                    <td>
                        @if ($room['is_maintenance'])
                            <span class="badge-maintenance">Maintenance</span>
                        @else
                            <span class="badge-normal">Normal</span>
                        @endif
                    </td>
                    <td>{{ $room['total_complaints'] }}</td>
                    <td>{{ $room['open'] }}</td>
                    <td>{{ $room['in_progress'] }}</td>
                    <td>{{ $room['resolved'] }}</td>
                    <td>{{ $room['rejected'] }}</td>
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
