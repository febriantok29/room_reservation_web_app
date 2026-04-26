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

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-open {
            background: #dc3545;
            color: #fff;
        }

        .badge-in_progress {
            background: #ffc107;
            color: #333;
        }

        .badge-resolved {
            background: #28a745;
            color: #fff;
        }

        .badge-rejected {
            background: #6c757d;
            color: #fff;
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
    <h1>Laporan Komplain &amp; Kerusakan Fasilitas</h1>
    <div class="subtitle">Dicetak pada {{ now()->format('d M Y H:i') }} WIB</div>

    <div class="summary-box">
        <span><strong>Total:</strong> {{ $summary['total'] }}</span>
        <span><strong>Terbuka:</strong> {{ $summary['open'] }}</span>
        <span><strong>Proses:</strong> {{ $summary['in_progress'] }}</span>
        <span><strong>Selesai:</strong> {{ $summary['resolved'] }}</span>
        <span><strong>Ditolak:</strong> {{ $summary['rejected'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ruangan</th>
                <th>Pelapor</th>
                <th>Fasilitas</th>
                <th>Judul</th>
                <th>Status</th>
                <th>Tanggal Lapor</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($complaints as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->room?->name ?? '-' }}</td>
                    <td>{{ $c->reporter?->full_name ?? '-' }}</td>
                    <td>{{ $c->facility?->name ?? '-' }}</td>
                    <td>{{ $c->title }}</td>
                    <td><span class="badge badge-{{ $c->status }}">{{ strtoupper($c->status) }}</span></td>
                    <td>{{ $c->created_at?->format('d/m/Y') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($c->resolution_notes ?? '-', 40) }}</td>
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
