@extends('adminlte::page')

@section('title', 'Laporan Aktivitas per Divisi')

<x-admin.report-header title="Laporan Aktivitas per Divisi" subtitle="Rekap reservasi ruangan berdasarkan divisi pemohon." />

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
    <x-admin.report-date-filter action="{{ route('admin.reports.division-activity') }}" :date-from="$dateFrom ?? ''" :date-to="$dateTo ?? ''" />

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
                                <td>{{ $r->start_time_local?->format('d/m/Y H:i') }}</td>
                                <td>{{ $r->end_time_local?->format('d/m/Y H:i') }}</td>
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
