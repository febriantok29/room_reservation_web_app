@extends('adminlte::page')

@section('title', 'Laporan Aktivitas Karyawan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Aktivitas Karyawan</h1>
            <div class="page-subtitle">Rekap aktivitas reservasi per pengguna dalam periode tertentu.</div>
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
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #007bff;">
                <span class="info-box-icon bg-primary"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Reservasi</span>
                    <span class="info-box-number">{{ $summary['total_reservations'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #17a2b8;">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pengguna Aktif</span>
                    <span class="info-box-number">{{ $summary['total_users'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #6c757d;">
                <span class="info-box-icon bg-secondary"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Periode</span>
                    <span class="info-box-number" style="font-size:.95rem;">
                        @if ($summary['date_from'] && $summary['date_to'])
                            {{ \Carbon\Carbon::parse($summary['date_from'])->format('d/m/Y') }}
                            – {{ \Carbon\Carbon::parse($summary['date_to'])->format('d/m/Y') }}
                        @else
                            Semua data
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.user-activity') }}">
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
                    <div class="col-lg-4 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1 d-block">Pengguna</label>
                        @include('admin.reports.partials.user_filter_pills', [
                            'users' => $users,
                            'selected' => $userFilters ?? [],
                        ])
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.user-activity') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Per-User Summary --}}
    @if ($byUser->isNotEmpty())
        <div class="card card-admin mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-user-chart mr-1"></i> Ringkasan per Pengguna</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama</th>
                                <th>No. Karyawan</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Disetujui</th>
                                <th class="text-center">Selesai</th>
                                <th class="text-center">Ditolak</th>
                                <th class="text-center">Dibatalkan</th>
                                <th class="text-center">Menunggu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($byUser as $row)
                                <tr>
                                    <td>{{ $row['full_name'] }}</td>
                                    <td>{{ $row['employee_id'] }}</td>
                                    <td class="text-center font-weight-bold">{{ $row['total'] }}</td>
                                    <td class="text-center">{{ $row['approved'] }}</td>
                                    <td class="text-center">{{ $row['completed'] }}</td>
                                    <td class="text-center">{{ $row['rejected'] }}</td>
                                    <td class="text-center">{{ $row['cancelled'] }}</td>
                                    <td class="text-center">{{ $row['pending'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Table --}}
    <div class="card card-admin">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Detail Reservasi</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width:140px;">ID Reservasi</th>
                            <th>Pengguna</th>
                            <th>No. Karyawan</th>
                            <th>Ruangan</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th class="text-center">Snack</th>
                            <th class="text-center">Makan Siang</th>
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
                                <td class="text-nowrap">{{ $r->start_time->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">{{ $r->end_time->format('d/m/Y H:i') }}</td>
                                <td><span class="badge badge-{{ $badge }}">{{ $label }}</span></td>
                                <td class="text-center">
                                    @if ($r->with_snack)
                                        <i class="fas fa-check text-success"></i>
                                    @else
                                        <i class="fas fa-times text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($r->with_lunch)
                                        <i class="fas fa-check text-success"></i>
                                    @else
                                        <i class="fas fa-times text-muted"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data aktivitas pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
