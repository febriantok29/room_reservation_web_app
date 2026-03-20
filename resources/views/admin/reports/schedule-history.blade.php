@extends('adminlte::page')

@section('title', 'Laporan Jadwal & Histori Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Jadwal &amp; Histori Reservasi</h1>
            <div class="page-subtitle">Seluruh riwayat reservasi ruangan berdasarkan filter periode dan status.</div>
        </div>
        <div class="d-flex" style="gap:.5rem;">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    {{-- Summary Cards --}}
    <div class="row mb-3">
        @foreach ([['label' => 'Total', 'key' => 'total', 'color' => '#6c757d', 'bg' => 'secondary', 'icon' => 'list'], ['label' => 'Menunggu', 'key' => 'pending', 'color' => '#ffc107', 'bg' => 'warning', 'icon' => 'clock'], ['label' => 'Disetujui', 'key' => 'approved', 'color' => '#28a745', 'bg' => 'success', 'icon' => 'check-circle'], ['label' => 'Selesai', 'key' => 'completed', 'color' => '#007bff', 'bg' => 'primary', 'icon' => 'flag-checkered'], ['label' => 'Ditolak', 'key' => 'rejected', 'color' => '#dc3545', 'bg' => 'danger', 'icon' => 'times-circle'], ['label' => 'Dibatalkan', 'key' => 'cancelled', 'color' => '#fd7e14', 'bg' => 'warning', 'icon' => 'ban']] as $card)
            <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                <div class="info-box shadow-none mb-0" style="border-left:4px solid {{ $card['color'] }};">
                    <span class="info-box-icon bg-{{ $card['bg'] }}"><i class="fas fa-{{ $card['icon'] }}"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $card['label'] }}</span>
                        <span class="info-box-number">{{ $summary[$card['key']] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.schedule-history') }}">
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="{{ $dateFrom ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="{{ $dateTo ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(($statusFilter ?? '') === 'pending')>Menunggu</option>
                            <option value="approved" @selected(($statusFilter ?? '') === 'approved')>Disetujui</option>
                            <option value="completed" @selected(($statusFilter ?? '') === 'completed')>Selesai</option>
                            <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Ditolak</option>
                            <option value="cancelled" @selected(($statusFilter ?? '') === 'cancelled')>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="small mb-1">Ruangan</label>
                        <select name="room_id" class="form-control form-control-sm">
                            <option value="">Semua Ruangan</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(($roomFilter ?? '') === $room->id)>
                                    {{ $room->name }} (Lt. {{ $room->floor }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.schedule-history') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            Periode: {{ \Carbon\Carbon::parse($summary['date_from'])->format('d/m/Y') }}
            – {{ \Carbon\Carbon::parse($summary['date_to'])->format('d/m/Y') }}
            &bull; Menampilkan {{ $reservations->count() }} reservasi.
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width:140px;">ID Reservasi</th>
                            <th>Pemohon</th>
                            <th>No. Karyawan</th>
                            <th>Ruangan</th>
                            <th>Lantai</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th class="text-center">Pengunjung</th>
                            <th>Status</th>
                            <th>Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $r)
                            @php
                                $badge = match ($r->status) {
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'completed' => 'primary',
                                    'rejected' => 'danger',
                                    default => 'secondary',
                                };
                                $label = match ($r->status) {
                                    'pending' => 'Menunggu',
                                    'approved' => 'Disetujui',
                                    'completed' => 'Selesai',
                                    'rejected' => 'Ditolak',
                                    'cancelled' => 'Dibatalkan',
                                    default => $r->status,
                                };
                            @endphp
                            <tr>
                                <td class="text-monospace small">{{ $r->id }}</td>
                                <td>{{ $r->user?->full_name ?? '-' }}</td>
                                <td>{{ $r->user?->employee_id ?? '-' }}</td>
                                <td>{{ $r->room?->name ?? '-' }}</td>
                                <td>{{ $r->room?->floor ? 'Lt. ' . $r->room->floor : '-' }}</td>
                                <td class="text-nowrap">{{ $r->start_time->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">{{ $r->end_time->format('d/m/Y H:i') }}</td>
                                <td class="text-center">{{ $r->visitor_count }}</td>
                                <td><span class="badge badge-{{ $badge }}">{{ $label }}</span></td>
                                <td style="max-width:200px; white-space:normal; word-break:break-word;">
                                    {{ $r->purpose ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data reservasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
