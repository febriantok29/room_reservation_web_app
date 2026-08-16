@extends('adminlte::page')

@section('title', 'Laporan Aktivitas Karyawan')

<x-admin.report-header title="Laporan Aktivitas Karyawan" subtitle="Rekap aktivitas reservasi per pengguna dalam periode tertentu." />

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
    <x-admin.report-date-filter action="{{ route('admin.reports.user-activity') }}" :date-from="$dateFrom ?? ''" :date-to="$dateTo ?? ''">
        <label class="small mb-1 d-block">Pengguna</label>
        @include('admin.reports.partials.user_filter_pills', [
            'users' => $users,
            'selected' => $userFilters ?? [],
        ])
    </x-admin.report-date-filter>

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
