@extends('adminlte::page')

@section('title', 'Laporan Komplain & Kerusakan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Laporan Komplain &amp; Kerusakan</h1>
            <div class="page-subtitle">Rekap pengaduan kerusakan fasilitas dan tindak lanjut penyelesaian.</div>
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
            <form method="GET" action="{{ route('admin.reports.complaints') }}">
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
                        <label class="small mb-1 d-block">Status</label>
                        @include('admin.reports.partials.status_filter_pills', [
                            'options' => [
                                'open' => 'Terbuka',
                                'in_progress' => 'Dikerjakan',
                                'resolved' => 'Selesai',
                                'rejected' => 'Ditolak',
                            ],
                            'selected' => $statusFilters ?? [],
                        ])
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="small mb-1 d-block">Ruangan</label>
                        @include('admin.reports.partials.room_filter_pills', [
                            'rooms' => $rooms,
                            'selected' => $roomFilters ?? [],
                        ])
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.reports.complaints') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            {{ $summary['total_facilities'] }} fasilitas &bull; {{ $summary['total'] }} total komplain.
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Fasilitas</th>
                            <th class="text-center">Total Komplain</th>
                            <th class="text-center">Terbuka</th>
                            <th class="text-center">Dikerjakan</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Ditolak</th>
                            <th>Ruangan Terkait</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byFacility as $f)
                            <tr>
                                <td class="font-weight-bold">{{ $f['facility_name'] }}</td>
                                <td class="text-center font-weight-bold">{{ $f['total'] }}</td>
                                <td class="text-center">
                                    @if ($f['open'] > 0)
                                    <span class="badge badge-warning">{{ $f['open'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($f['in_progress'] > 0)
                                    <span class="badge badge-info">{{ $f['in_progress'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($f['resolved'] > 0)
                                    <span class="badge badge-success">{{ $f['resolved'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($f['rejected'] > 0)
                                    <span class="badge badge-danger">{{ $f['rejected'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($f['rooms'] as $roomName)
                                        <span class="badge badge-light border mr-1">{{ $roomName }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data komplain.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
