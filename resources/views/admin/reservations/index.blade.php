@extends('adminlte::page')

@section('title', 'Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: .75rem;">
        <div>
            <h1 class="m-0">Reservasi</h1>
            <div class="page-subtitle">Kelola jadwal pemakaian ruangan dan status reservasi pengguna.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-plus"></i> Tambah Reservasi
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Daftar Reservasi</h3>
        </div>

        {{-- Filter Section --}}
        <div class="card-body border-bottom">
            <form action="{{ route('admin.reservations') }}" method="GET">
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="form-group mb-0">
                            <input type="text" name="q" class="form-control form-control-sm"
                                placeholder="Cari ID reservasi, nama pengguna, atau ruangan..."
                                value="{{ $searchQuery ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group mb-0">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="pending" @selected(($statusFilter ?? '') === 'pending')>Menunggu</option>
                                <option value="approved" @selected(($statusFilter ?? '') === 'approved')>Disetujui</option>
                                <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Ditolak</option>
                                <option value="completed" @selected(($statusFilter ?? '') === 'completed')>Selesai</option>
                                <option value="cancelled" @selected(($statusFilter ?? '') === 'cancelled')>Dibatalkan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body border-bottom py-2 text-sm text-muted">
            Menampilkan {{ $reservations->count() }} dari {{ $reservations->total() }} reservasi.
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Ruangan</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user?->full_name ?? '-' }}</td>
                            <td>{{ $reservation->room?->name ?? '-' }}</td>
                            <td>{{ $reservation->start_time_label }}</td>
                            <td>{{ $reservation->end_time_label }}</td>
                            <td>
                                <span
                                    class="badge
                                    @if ($reservation->status === 'approved') bg-success
                                    @elseif($reservation->status === 'pending') bg-warning
                                    @elseif($reservation->status === 'rejected') bg-danger
                                    @else bg-secondary @endif">
                                    {{ match ($reservation->status) {
                                        'pending' => 'MENUNGGU',
                                        'approved' => 'DISETUJUI',
                                        'rejected' => 'DITOLAK',
                                        'completed' => 'SELESAI',
                                        'cancelled' => 'DIBATALKAN',
                                        default => strtoupper($reservation->status),
                                    } }}
                                </span>
                            </td>
                            <td>
                                <div class="table-action-group">
                                    {{-- Detail button (always available) --}}
                                    <a href="{{ route('admin.reservations.edit', $reservation->id) }}"
                                        class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>

                                    {{-- Edit button (only for pending) --}}
                                    @if ($reservation->status === 'pending')
                                        <a href="{{ route('admin.reservations.edit', $reservation->id) }}"
                                            class="btn btn-warning btn-xs">
                                            <i class="fas fa-edit"></i> Ubah
                                        </a>
                                    @endif

                                    {{-- Complete button (for approved after end time) --}}
                                    @if ($reservation->status === 'approved' && $reservation->end_time_local->lte(now()))
                                        <form action="{{ route('admin.reservations.complete', $reservation->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Tandai reservasi ini sebagai selesai?')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs">
                                                <i class="fas fa-check"></i> Selesai
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Cancel button (only for pending) --}}
                                    @if ($reservation->status === 'pending')
                                        <form action="{{ route('admin.reservations.destroy', $reservation->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">
                                                <i class="fas fa-times"></i> Batalkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state-cell">
                                <div class="empty-icon"><i class="far fa-calendar-times"></i></div>
                                <div class="empty-title">Belum ada data reservasi</div>
                                <div class="empty-desc">Buat reservasi baru untuk mulai pengelolaan jadwal ruangan.</div>
                                <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambah Reservasi
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $reservations->links() }}
        </div>
    </div>
@stop

@include('admin.partials.timezone_detector')
