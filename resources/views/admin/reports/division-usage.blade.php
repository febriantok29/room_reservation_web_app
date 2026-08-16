@extends('adminlte::page')

@section('title', 'Laporan Pemakaian per Divisi')

<x-admin.report-header title="Laporan Pemakaian per Divisi" subtitle="Rekapitulasi durasi dan pemakaian ruangan berdasarkan divisi (reservasi disetujui &amp; selesai)." />

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
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #fd7e14;">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Jam Pemakaian</span>
                    <span class="info-box-number">{{ $summary['total_hours'] }} <small>jam</small></span>
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
                <span class="info-box-icon bg-secondary"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Divisi Aktif</span>
                    <span class="info-box-number">{{ $byDivision->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.division-usage') }}">
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
                            <a href="{{ route('admin.reports.division-usage') }}" class="btn btn-secondary btn-sm">
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
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Ringkasan Pemakaian per Divisi</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Divisi</th>
                                <th>Kode</th>
                                <th class="text-center">Jml. Reservasi</th>
                                <th class="text-center">Total Jam</th>
                                <th class="text-center">Rata-rata Jam</th>
                                <th class="text-center">Pengunjung</th>
                                <th>Ruangan Dipakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($byDivision as $row)
                                <tr>
                                    <td class="font-weight-bold">{{ $row['division_name'] }}</td>
                                    <td>
                                        @if ($row['division_code'] !== '-')
                                            <span class="badge badge-info">{{ $row['division_code'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $row['reservation_count'] }}</td>
                                    <td class="text-center font-weight-bold text-warning">{{ $row['total_hours'] }} jam
                                    </td>
                                    <td class="text-center">{{ $row['avg_hours'] }} jam</td>
                                    <td class="text-center">{{ $row['total_visitors'] }}</td>
                                    <td>
                                        @foreach ($row['rooms_used'] as $room)
                                            <span class="badge badge-light border mr-1">{{ $room }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Room Breakdown per Division --}}
        @foreach ($byDivision as $row)
            @if (!empty($row['room_breakdown']))
                <div class="card card-admin mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title">
                            <i class="fas fa-door-open mr-1"></i>
                            Rincian Ruangan —
                            @if ($row['division_code'] !== '-')
                                <span class="badge badge-info mr-1">{{ $row['division_code'] }}</span>
                            @endif
                            {{ $row['division_name'] }}
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-warning">{{ $row['total_hours'] }} jam total</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nama Ruangan</th>
                                        <th>Lantai</th>
                                        <th class="text-center">Jml. Pemakaian</th>
                                        <th class="text-center">Total Jam</th>
                                        <th class="text-center">Pengunjung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($row['room_breakdown'] as $rb)
                                        <tr>
                                            <td>{{ $rb['room_name'] }}</td>
                                            <td>{{ $rb['floor'] !== '-' ? 'Lantai ' . $rb['floor'] : '-' }}</td>
                                            <td class="text-center">{{ $rb['count'] }}×</td>
                                            <td class="text-center font-weight-bold">{{ $rb['hours'] }} jam</td>
                                            <td class="text-center">{{ $rb['visitors'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <div class="card card-admin">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                Tidak ada data pemakaian ruangan pada periode ini.
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .opacity-50 {
            opacity: .5;
        }
    </style>
@stop
