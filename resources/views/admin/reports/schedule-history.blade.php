@extends('adminlte::page')

@section('title', 'Riwayat Pemesanan Ruangan')

<x-admin.report-header title="Riwayat Pemesanan Ruangan" subtitle="Seluruh riwayat reservasi ruangan berdasarkan filter periode dan status." />

@section('content')
    @include('admin.partials.flash_message')

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
                        <label class="small mb-1 d-block">Status</label>
                        @include('admin.reports.partials.status_filter_pills', [
                            'options' => [
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'completed' => 'Selesai',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
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
                            <a href="{{ route('admin.reports.schedule-history') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            @if ($summary['date_from'] && $summary['date_to'])
                Periode: {{ \Carbon\Carbon::parse($summary['date_from'])->format('d/m/Y') }}
                – {{ \Carbon\Carbon::parse($summary['date_to'])->format('d/m/Y') }}
                &bull;
            @endif
            {{ $summary['total_rooms'] }} ruangan &bull; {{ $summary['total'] }} total reservasi.
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ruangan</th>
                            <th class="text-center">Lantai</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Menunggu</th>
                            <th class="text-center">Disetujui</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Ditolak</th>
                            <th class="text-center">Dibatalkan</th>
                            <th class="text-center">Total Jam</th>
                            <th class="text-center">Total Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byRoom as $r)
                            <tr>
                                <td>{{ $r['room_name'] }}</td>
                                <td class="text-center">{{ $r['floor'] ? 'Lt. ' . $r['floor'] : '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $r['total'] }}</td>
                                <td class="text-center">
                                    @if ($r['pending'] > 0)
                                    <span class="badge badge-warning">{{ $r['pending'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($r['approved'] > 0)
                                    <span class="badge badge-success">{{ $r['approved'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($r['completed'] > 0)
                                    <span class="badge badge-primary">{{ $r['completed'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($r['rejected'] > 0)
                                    <span class="badge badge-danger">{{ $r['rejected'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($r['cancelled'] > 0)
                                    <span class="badge badge-secondary">{{ $r['cancelled'] }}</span>@else<span
                                            class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $r['total_hours'] }} j</td>
                                <td class="text-center">{{ $r['total_visitors'] }}</td>
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
