@extends('adminlte::page')

@section('title', 'Laporan Aktivitas per Divisi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Aktivitas per Divisi</h1>
            <div class="page-subtitle">Rekapitulasi jumlah dan status pemesanan ruangan berdasarkan divisi.</div>
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

    {{-- Filter --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.division-activity') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="{{ $dateFrom ?? '' }}">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="{{ $dateTo ?? '' }}">
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.division-activity') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Single Table: Per-Divisi --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Divisi</th>
                            <th>Kode</th>
                            <th class="text-center">Total Pesanan</th>
                            <th class="text-center">Disetujui</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Ditolak</th>
                            <th class="text-center">Dibatalkan</th>
                            <th class="text-center">Menunggu</th>
                            <th class="text-center">Total Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byDivision as $row)
                            <tr>
                                <td class="font-weight-bold">{{ $row['division_name'] }}</td>
                                <td>
                                    @if ($row['division_code'] !== '-')
                                        <span class="badge badge-info">{{ $row['division_code'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $row['total'] }}</td>
                                <td class="text-center"><span class="badge badge-success">{{ $row['approved'] }}</span></td>
                                <td class="text-center"><span class="badge badge-primary">{{ $row['completed'] }}</span>
                                </td>
                                <td class="text-center"><span class="badge badge-danger">{{ $row['rejected'] }}</span></td>
                                <td class="text-center"><span class="badge badge-secondary">{{ $row['cancelled'] }}</span>
                                </td>
                                <td class="text-center"><span class="badge badge-warning">{{ $row['pending'] }}</span></td>
                                <td class="text-center">{{ $row['visitors'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data pemesanan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop


@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Aktivitas per Divisi</h1>
            <div class="page-subtitle">Rekap reservasi ruangan berdasarkan divisi pemohon.</div>
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
                <span class="info-box-icon bg-primary"><i class="fas fa-list-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Reservasi</span>
                    <span class="info-box-number">{{ $summary['total_reservations'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #28a745;">
                <span class="info-box-icon bg-success"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Divisi Aktif</span>
                    <span class="info-box-number">{{ $byDivision->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #17a2b8;">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Pengunjung</span>
                    <span class="info-box-number">{{ $summary['total_visitors'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #6c757d;">
                <span class="info-box-icon bg-secondary"><i class="fas fa-calendar-range"></i></span>
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
            <form method="GET" action="{{ route('admin.reports.division-activity') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="{{ $dateFrom ?? '' }}">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
                        <label class="small mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="{{ $dateTo ?? '' }}">
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.division-activity') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Per-Division Summary Table --}}
    @if ($byDivision->isNotEmpty())
        <div class="card card-admin mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Ringkasan per Divisi</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Divisi</th>
                                <th>Kode</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Disetujui</th>
                                <th class="text-center">Selesai</th>
                                <th class="text-center">Ditolak</th>
                                <th class="text-center">Dibatalkan</th>
                                <th class="text-center">Menunggu</th>
                                <th class="text-center">Pengunjung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($byDivision as $row)
                                <tr>
                                    <td>{{ $row['division_name'] }}</td>
                                    <td>
                                        @if ($row['division_code'] !== '-')
                                            <span class="badge badge-info">{{ $row['division_code'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $row['total'] }}</td>
                                    <td class="text-center"><span
                                            class="badge badge-success">{{ $row['approved'] }}</span>
                                    </td>
                                    <td class="text-center"><span
                                            class="badge badge-primary">{{ $row['completed'] }}</span></td>
                                    <td class="text-center"><span
                                            class="badge badge-danger">{{ $row['rejected'] }}</span>
                                    </td>
                                    <td class="text-center"><span
                                            class="badge badge-secondary">{{ $row['cancelled'] }}</span></td>
                                    <td class="text-center"><span
                                            class="badge badge-warning">{{ $row['pending'] }}</span>
                                    </td>
                                    <td class="text-center">{{ $row['visitors'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Reservations --}}
    <div class="card card-admin">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Detail Reservasi</h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $reservations->count() }} data</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID Reservasi</th>
                            <th>Pemohon</th>
                            <th>No. Karyawan</th>
                            <th>Divisi</th>
                            <th>Ruangan</th>
                            <th>Tgl Mulai</th>
                            <th>Tgl Selesai</th>
                            <th>Status</th>
                            <th class="text-center">Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $r)
                            <tr>
                                <td><code>{{ $r->id }}</code></td>
                                <td>{{ $r->user?->full_name ?? '-' }}</td>
                                <td><code>{{ $r->user?->employee_id ?? '-' }}</code></td>
                                <td>
                                    @if ($r->user?->division)
                                        <span class="badge badge-info">{{ $r->user->division->code }}</span>
                                        {{ $r->user->division->name }}
                                    @else
                                        <span class="text-muted">Admin / Tanpa Divisi</span>
                                    @endif
                                </td>
                                <td>{{ $r->room?->name ?? '-' }}</td>
                                <td>{{ $r->start_time?->format('d/m/Y H:i') }}</td>
                                <td>{{ $r->end_time?->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                        $badgeMap = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'completed' => 'primary',
                                            'rejected' => 'danger',
                                            'cancelled' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $badgeMap[$r->status] ?? 'light' }}">
                                        {{ ucfirst($r->status) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $r->visitor_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">Tidak ada data reservasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
