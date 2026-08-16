@extends('adminlte::page')

@section('title', 'Rekapitulasi Penggunaan Ruangan')

<x-admin.report-header title="Rekapitulasi Penggunaan Ruangan" subtitle="Rekap pemakaian dan durasi penggunaan setiap ruangan." />

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
    <x-admin.report-date-filter action="{{ route('admin.reports.usage') }}" :date-from="$dateFrom ?? ''" :date-to="$dateTo ?? ''">
        <label class="small mb-1 d-block">Ruangan</label>
        @include('admin.reports.partials.room_filter_pills', [
            'rooms' => $rooms,
            'selected' => $roomFilters ?? [],
        ])
    </x-admin.report-date-filter>

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
