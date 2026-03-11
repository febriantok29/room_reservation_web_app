@extends('adminlte::page')

@section('title', 'Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Reservasi</h1>
            <div class="page-subtitle">Kelola jadwal pemakaian ruangan dan status reservasi pengguna.</div>
        </div>
        <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Reservasi
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    {{-- Filter Bar --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form action="{{ route('admin.reservations') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-6 mb-2 mb-lg-0">
                        <input type="text" name="q" class="form-control form-control-sm"
                            placeholder="Cari ID reservasi, nama pengguna, atau ruangan..."
                            value="{{ $searchQuery ?? '' }}">
                    </div>
                    <div class="col-lg-4 col-md-4 mb-2 mb-lg-0">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(($statusFilter ?? '') === 'pending')>Menunggu</option>
                            <option value="approved" @selected(($statusFilter ?? '') === 'approved')>Disetujui</option>
                            <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Ditolak</option>
                            <option value="completed" @selected(($statusFilter ?? '') === 'completed')>Selesai</option>
                            <option value="cancelled" @selected(($statusFilter ?? '') === 'cancelled')>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            Menampilkan {{ $reservations->count() }} dari {{ $reservations->total() }} reservasi.
        </div>
    </div>

    {{-- Reservation Cards --}}
    <div class="row">
        @forelse ($reservations as $reservation)
            @php
                $sColor = match ($reservation->status) {
                    'pending' => ['badge' => 'warning', 'border' => '#ffc107', 'icon' => 'fas fa-clock'],
                    'approved' => ['badge' => 'success', 'border' => '#28a745', 'icon' => 'fas fa-check-circle'],
                    'rejected' => ['badge' => 'danger', 'border' => '#dc3545', 'icon' => 'fas fa-times-circle'],
                    'completed' => ['badge' => 'primary', 'border' => '#007bff', 'icon' => 'fas fa-flag-checkered'],
                    default => ['badge' => 'secondary', 'border' => '#6c757d', 'icon' => 'fas fa-ban'],
                };
                $sLabel = match ($reservation->status) {
                    'pending' => 'MENUNGGU',
                    'approved' => 'DISETUJUI',
                    'rejected' => 'DITOLAK',
                    'completed' => 'SELESAI',
                    'cancelled' => 'DIBATALKAN',
                    default => strtoupper($reservation->status),
                };
            @endphp
            <div class="col-xl-6 col-12 mb-3">
                <div class="card card-admin h-100" style="border-left:4px solid {{ $sColor['border'] }};">

                    {{-- Card Header: ID + Status Badge --}}
                    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted small font-weight-bold">
                            <i class="fas fa-hashtag mr-1" style="font-size:.7rem;"></i>{{ $reservation->id }}
                        </span>
                        <span class="badge badge-{{ $sColor['badge'] }}">
                            <i class="{{ $sColor['icon'] }} mr-1"></i>{{ $sLabel }}
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body py-3 px-3">

                        {{-- Room --}}
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-door-open mr-2"
                                style="color:{{ $sColor['border'] }};width:16px;flex-shrink:0;font-size:.95rem;"></i>
                            <span class="font-weight-bold"
                                style="font-size:.95rem;">{{ $reservation->room?->name ?? '-' }}</span>
                            @if ($reservation->room?->floor)
                                <span class="text-muted small ml-2">· Lantai {{ $reservation->room->floor }}</span>
                            @endif
                        </div>

                        {{-- Date / Time --}}
                        <div class="d-flex align-items-center mb-2 text-sm">
                            <i class="fas fa-calendar-alt text-muted mr-2" style="width:16px;flex-shrink:0;"></i>
                            <span>{{ $reservation->start_time_label }}</span>
                            <span class="mx-2 text-muted">&ndash;</span>
                            <span>{{ $reservation->end_time_label }}</span>
                        </div>

                        {{-- User + Visitors --}}
                        <div class="d-flex align-items-center text-sm">
                            <i class="fas fa-user text-muted mr-2" style="width:16px;flex-shrink:0;"></i>
                            <span>{{ $reservation->user?->full_name ?? '-' }}</span>
                            <span class="mx-2 text-muted">·</span>
                            <i class="fas fa-users text-muted mr-1"></i>
                            <span>{{ $reservation->visitor_count }} orang</span>
                        </div>

                        {{-- Purpose --}}
                        @if ($reservation->purpose)
                            <div class="d-flex align-items-start text-sm text-muted mt-2">
                                <i class="fas fa-clipboard-list mr-2 mt-1" style="width:16px;flex-shrink:0;"></i>
                                <span>{{ Str::limit($reservation->purpose, 90) }}</span>
                            </div>
                        @endif

                    </div>

                    {{-- Card Footer: Actions --}}
                    <div class="card-footer py-2 px-3 d-flex flex-wrap" style="gap:.3rem;">
                        <a href="{{ route('admin.reservations.edit', $reservation->id) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i> Detail
                        </a>

                        @if ($reservation->status === 'pending')
                            <a href="{{ route('admin.reservations.edit', $reservation->id) }}"
                                class="btn btn-warning btn-xs">
                                <i class="fas fa-edit"></i> Ubah
                            </a>
                        @endif

                        @if ($reservation->status === 'approved' && $reservation->end_time_local->lte(now()))
                            <form action="{{ route('admin.reservations.complete', $reservation->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Tandai reservasi ini sebagai selesai?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-xs">
                                    <i class="fas fa-check-circle"></i> Selesaikan
                                </button>
                            </form>
                        @endif

                        @if ($reservation->status === 'pending')
                            <form action="{{ route('admin.reservations.destroy', $reservation->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fas fa-times"></i> Batalkan
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-admin">
                    <div class="card-body">
                        <div class="empty-state-cell">
                            <div class="empty-icon"><i class="far fa-calendar-times"></i></div>
                            <div class="empty-title">Belum ada data reservasi</div>
                            <div class="empty-desc">Buat reservasi baru untuk mulai pengelolaan jadwal ruangan.</div>
                            <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Reservasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($reservations->hasPages())
        <div class="d-flex justify-content-center mt-1">
            {{ $reservations->links() }}
        </div>
    @endif
@stop

@include('admin.partials.timezone_detector')
