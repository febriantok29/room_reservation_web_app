@extends('adminlte::page')

@section('title', 'Laporan Status Maintenance Ruangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Status Maintenance Ruangan</h1>
            <div class="page-subtitle">Kondisi ruangan beserta riwayat komplain dan status maintenance.</div>
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
            <form method="GET" action="{{ route('admin.reports.maintenance') }}">
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
                        <label class="small mb-1 d-block">Ruangan</label>
                        @include('admin.reports.partials.room_filter_pills', [
                            'rooms' => $allRoomsList,
                            'selected' => $roomFilters ?? [],
                        ])
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.maintenance') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Room Table --}}
    <div class="card card-admin">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Data Ruangan</h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $rooms->count() }} ruangan</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ruangan</th>
                            <th>Lantai</th>
                            <th class="text-center">Kapasitas</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Total Komplain</th>
                            <th class="text-center">Terbuka</th>
                            <th class="text-center">Dikerjakan</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Ditolak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $room)
                            <tr>
                                <td class="font-weight-bold">{{ $room['name'] }}</td>
                                <td>{{ $room['floor'] }}</td>
                                <td class="text-center">{{ $room['capacity'] }}</td>
                                <td class="text-center">
                                    @if ($room['is_maintenance'])
                                        <span class="badge badge-danger"><i class="fas fa-tools mr-1"></i>Maintenance</span>
                                    @else
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Normal</span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ $room['total_complaints'] }}</td>
                                <td class="text-center">
                                    @if ($room['open'] > 0)
                                        <span class="badge badge-warning">{{ $room['open'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($room['in_progress'] > 0)
                                        <span class="badge badge-info">{{ $room['in_progress'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($room['resolved'] > 0)
                                        <span class="badge badge-success">{{ $room['resolved'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($room['rejected'] > 0)
                                        <span class="badge badge-danger">{{ $room['rejected'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">Tidak ada data ruangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
