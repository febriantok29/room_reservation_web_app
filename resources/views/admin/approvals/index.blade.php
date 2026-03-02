@extends('adminlte::page')

@section('title', 'Antrian Persetujuan')

@section('content_header')
    <div>
        <h1 class="m-0">Antrian Persetujuan</h1>
        <div class="page-subtitle">Tinjau dan putuskan permintaan reservasi yang masih menunggu.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Reservasi Menunggu Persetujuan</h3>
            <div class="card-tools">
                <form action="{{ route('admin.approvals') }}" method="GET" class="input-group input-group-sm"
                    style="width: 300px;">
                    <input type="text" name="q" class="form-control" placeholder="Cari ID, pengguna, ruangan"
                        value="{{ $searchQuery ?? '' }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body border-bottom py-2 text-sm text-muted">
            Menampilkan {{ $pendingReservations->count() }} dari {{ $pendingReservations->total() }} antrian.
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
                        <th>Jumlah Pengunjung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user?->full_name ?? '-' }}</td>
                            <td>{{ $reservation->room?->name ?? '-' }}</td>
                            <td>{{ $reservation->start_time_label }}</td>
                            <td>{{ $reservation->end_time_label }}</td>
                            <td>{{ $reservation->visitor_count }}</td>
                            <td>
                                <div class="table-action-group">
                                    <form action="{{ route('admin.approvals.approve', $reservation->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-xs">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.approvals.reject', $reservation->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin menolak reservasi ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state-cell">
                                <div class="empty-icon"><i class="fas fa-clipboard-check"></i></div>
                                <div class="empty-title">Tidak ada antrian persetujuan</div>
                                <div class="empty-desc">Semua reservasi sudah diproses atau belum ada permintaan baru.</div>
                                <a href="{{ route('admin.reservations') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-list mr-1"></i> Lihat Semua Reservasi
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $pendingReservations->links() }}
        </div>
    </div>
@stop
