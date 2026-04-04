@extends('adminlte::page')

@section('title', 'Laporan Pemakaian Per-Ruangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Pemakaian Per-Ruangan</h1>
            <div class="page-subtitle">Rekapitulasi durasi, frekuensi, dan pengunjung untuk setiap ruangan.</div>
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
            <form method="GET" action="{{ route('admin.reports.usage') }}">
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
                    <div class="col-lg-4 col-md-12">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.usage') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Single Table: Per-Room Summary --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Ruangan</th>
                            <th class="text-center">Lantai</th>
                            <th class="text-center">Kapasitas</th>
                            <th class="text-center">Jml. Pemakaian</th>
                            <th class="text-center">Total Jam</th>
                            <th class="text-center">Rata-rata Jam</th>
                            <th class="text-center">Total Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byRoom as $row)
                            <tr>
                                <td class="font-weight-bold">{{ $row['room_name'] }}</td>
                                <td class="text-center">{{ $row['floor'] ? 'Lt. ' . $row['floor'] : '-' }}</td>
                                <td class="text-center">{{ $row['capacity'] }} orang</td>
                                <td class="text-center">{{ $row['reserved_count'] }}×</td>
                                <td class="text-center font-weight-bold text-warning">{{ $row['total_hours'] }} jam</td>
                                <td class="text-center">{{ $row['avg_hours'] }} jam</td>
                                <td class="text-center">{{ $row['total_visitors'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data pemakaian ruangan pada periode ini.
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
            <h1 class="m-0">Laporan Penggunaan Ruangan</h1>
            <div class="page-subtitle">Rekap pemakaian dan durasi penggunaan setiap ruangan.</div>
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
                <span class="info-box-icon bg-success"><i class="fas fa-door-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ruangan Terpakai</span>
                    <span class="info-box-number">{{ $summary['total_rooms_used'] }}</span>
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
                        {{ \Carbon\Carbon::parse($summary['date_from'])->format('d/m/Y') }}
                        – {{ \Carbon\Carbon::parse($summary['date_to'])->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.reports.usage') }}">
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
                    <div class="col-lg-4 col-md-12">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.usage') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Per-Room Summary --}}
    @if ($byRoom->isNotEmpty())
        <div class="card card-admin mb-3">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Ringkasan per Ruangan</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Ruangan</th>
                                <th>Lantai</th>
                                <th class="text-center">Jml Reservasi</th>
                                <th class="text-center">Total Jam</th>
                                <th class="text-center">Total Pengunjung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($byRoom as $row)
                                <tr>
                                    <td>{{ $row['room_name'] }}</td>
                                    <td>{{ $row['floor'] ? 'Lantai ' . $row['floor'] : '-' }}</td>
                                    <td class="text-center">{{ $row['reserved_count'] }}</td>
                                    <td class="text-center">{{ $row['total_hours'] }} jam</td>
                                    <td class="text-center">{{ $row['total_visitors'] }}</td>
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
                            <th>Ruangan</th>
                            <th>Pemohon</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-center">Pengunjung</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $r)
                            @php
                                $mins = $r->start_time->diffInMinutes($r->end_time);
                                $dur = $mins >= 60 ? floor($mins / 60) . 'j ' . $mins % 60 . 'm' : $mins . 'm';
                                $badge = $r->status === 'completed' ? 'primary' : 'success';
                                $label = $r->status === 'completed' ? 'Selesai' : 'Disetujui';
                            @endphp
                            <tr>
                                <td class="text-monospace small">{{ $r->id }}</td>
                                <td>{{ $r->room?->name ?? '-' }}</td>
                                <td>{{ $r->user?->full_name ?? '-' }}</td>
                                <td class="text-nowrap">{{ $r->start_time->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">{{ $r->end_time->format('d/m/Y H:i') }}</td>
                                <td class="text-center">{{ $dur }}</td>
                                <td class="text-center">{{ $r->visitor_count }}</td>
                                <td><span class="badge badge-{{ $badge }}">{{ $label }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
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
