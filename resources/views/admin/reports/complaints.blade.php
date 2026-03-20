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

    {{-- Summary Cards --}}
    <div class="row mb-3">
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #6c757d;">
                <span class="info-box-icon bg-secondary"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total</span>
                    <span class="info-box-number">{{ $summary['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #ffc107;">
                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Terbuka</span>
                    <span class="info-box-number">{{ $summary['open'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #17a2b8;">
                <span class="info-box-icon bg-info"><i class="fas fa-tools"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Dikerjakan</span>
                    <span class="info-box-number">{{ $summary['in_progress'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #28a745;">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Selesai</span>
                    <span class="info-box-number">{{ $summary['resolved'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-none mb-0" style="border-left:4px solid #dc3545;">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ditolak</span>
                    <span class="info-box-number">{{ $summary['rejected'] }}</span>
                </div>
            </div>
        </div>
    </div>

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
                        <label class="small mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="open" @selected(($statusFilter ?? '') === 'open')>Terbuka</option>
                            <option value="in_progress" @selected(($statusFilter ?? '') === 'in_progress')>Dikerjakan</option>
                            <option value="resolved" @selected(($statusFilter ?? '') === 'resolved')>Selesai</option>
                            <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
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
            Menampilkan {{ $complaints->count() }} komplain.
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width:140px;">ID Komplain</th>
                            <th>Ruangan</th>
                            <th>Pelapor</th>
                            <th>Fasilitas</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Tanggal Lapor</th>
                            <th>Penyelesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                            @php
                                $badge = match ($complaint->status) {
                                    'open' => 'warning',
                                    'in_progress' => 'info',
                                    'resolved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary',
                                };
                                $label = match ($complaint->status) {
                                    'open' => 'Terbuka',
                                    'in_progress' => 'Dikerjakan',
                                    'resolved' => 'Selesai',
                                    'rejected' => 'Ditolak',
                                    default => $complaint->status,
                                };
                            @endphp
                            <tr>
                                <td class="text-monospace small">{{ $complaint->id }}</td>
                                <td>{{ $complaint->room?->name ?? '-' }}</td>
                                <td>{{ $complaint->reporter?->full_name ?? '-' }}</td>
                                <td>{{ $complaint->facility?->name ?? '-' }}</td>
                                <td>{{ $complaint->complaint_title }}</td>
                                <td><span class="badge badge-{{ $badge }}">{{ $label }}</span></td>
                                <td class="text-nowrap">{{ $complaint->created_at?->format('d/m/Y') }}</td>
                                <td>{{ $complaint->resolver?->full_name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
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
