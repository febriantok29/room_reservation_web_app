@extends('adminlte::page')

@section('title', 'Dasbor Admin')

@section('content_header')
    <div>
        <h1 class="m-0">Dashboard Admin</h1>
        <div class="page-subtitle">Ringkasan operasional reservasi ruangan hari ini.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="mb-3 d-flex flex-wrap" style="gap: .5rem;">
        <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Buat Reservasi
        </a>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-door-open mr-1"></i> Tambah Ruangan
        </a>
        <a href="{{ route('admin.approvals') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-clipboard-check mr-1"></i> Buka Antrian Persetujuan
        </a>
    </div>

    {{-- Row 1: Primary Stats --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total_rooms'] }}</h3>
                    <p>Total Ruangan</p>
                </div>
                <div class="icon"><i class="fas fa-door-open"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['total_users'] }}</h3>
                    <p>Total Pengguna</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['pending_reservations'] }}</h3>
                    <p>Reservasi Menunggu</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $summary['approved_reservations'] }}</h3>
                    <p>Reservasi Disetujui</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    {{-- Row 2: Additional Stats --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #17a2b8;">
                <span class="info-box-icon bg-info"><i class="fas fa-calendar-day"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Reservasi Hari Ini</span>
                    <span class="info-box-number">{{ $summary['today_reservations'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #dc3545;">
                <span class="info-box-icon bg-danger"><i class="fas fa-tools"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ruangan Maintenance</span>
                    <span class="info-box-number">{{ $summary['maintenance_rooms'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <a href="{{ route('admin.complaints') }}" class="text-decoration-none">
                <div class="info-box shadow-none mb-0" style="border-left:4px solid #ffc107;">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Komplain Terbuka</span>
                        <span class="info-box-number">{{ $summary['open_complaints'] }}</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <a href="{{ route('admin.complaints') }}" class="text-decoration-none">
                <div class="info-box shadow-none mb-0" style="border-left:4px solid #6f42c1;">
                    <span class="info-box-icon" style="background:#6f42c1;"><i class="fas fa-wrench"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Komplain Dikerjakan</span>
                        <span class="info-box-number">{{ $summary['in_progress_complaints'] }}</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Latest Reservations --}}
        <div class="col-lg-7 col-12">
            <div class="card card-admin">
                <div class="card-header">
                    <h3 class="card-title">Reservasi Terbaru</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.reservations') }}" class="btn btn-tool btn-sm text-primary">
                            Lihat semua <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pengguna</th>
                                <th>Ruangan</th>
                                <th>Mulai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestReservations as $reservation)
                                <tr>
                                    <td class="small text-monospace">{{ $reservation->id }}</td>
                                    <td>{{ $reservation->user?->full_name ?? '-' }}</td>
                                    <td>{{ $reservation->room?->name ?? '-' }}</td>
                                    <td>{{ $reservation->start_time_label }}</td>
                                    <td>
                                        <span
                                            class="badge
                                            @if ($reservation->status === 'approved') badge-success
                                            @elseif($reservation->status === 'pending') badge-warning
                                            @elseif($reservation->status === 'rejected') badge-danger
                                            @elseif($reservation->status === 'completed') badge-primary
                                            @else badge-secondary @endif">
                                            {{ match ($reservation->status) {
                                                'pending' => 'MENUNGGU',
                                                'approved' => 'DISETUJUI',
                                                'rejected' => 'DITOLAK',
                                                'completed' => 'SELESAI',
                                                'cancelled' => 'DIBATALKAN',
                                                default => strtoupper($reservation->status),
                                            } }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="far fa-calendar-times fa-2x d-block mb-2"></i>
                                        Belum ada reservasi terbaru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Room Status Grid --}}
        <div class="col-lg-5 col-12">
            <div class="card card-admin">
                <div class="card-header">
                    <h3 class="card-title">Status Ruangan Hari Ini</h3>
                    <div class="card-tools">
                        <small class="text-muted">
                            <span class="badge badge-success mr-1">Tersedia</span>
                            <span class="badge badge-warning mr-1">Dipesan</span>
                            <span class="badge badge-danger">Maintenance</span>
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" style="gap:.5rem 0;">
                        @forelse($roomStatuses as $rs)
                            @php
                                if ($rs['maintenance']) {
                                    $bg = '#dc3545';
                                    $title = 'Maintenance';
                                    $icon = 'fa-tools';
                                } elseif ($rs['booked_today']) {
                                    $bg = '#ffc107';
                                    $title = 'Dipesan';
                                    $icon = 'fa-calendar-check';
                                } else {
                                    $bg = '#28a745';
                                    $title = 'Tersedia';
                                    $icon = 'fa-check';
                                }
                            @endphp
                            <div class="col-6 col-sm-4 mb-2">
                                <div class="d-flex align-items-center p-2 rounded"
                                    style="background:{{ $bg }}18; border:1px solid {{ $bg }}40;"
                                    title="{{ $rs['name'] }} — {{ $title }}">
                                    <i class="fas {{ $icon }} mr-2" style="color:{{ $bg }};"></i>
                                    <div style="min-width:0;">
                                        <div class="font-weight-bold text-truncate"
                                            style="font-size:.8rem; max-width:100px;">
                                            {{ $rs['name'] }}
                                        </div>
                                        <div class="text-muted" style="font-size:.7rem;">Lt. {{ $rs['floor'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-3">
                                <i class="fas fa-door-open fa-2x d-block mb-2"></i>
                                Belum ada data ruangan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@include('admin.partials.timezone_detector')
