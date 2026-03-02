@extends('adminlte::page')

@section('title', 'Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Reservasi</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-plus"></i> Tambah Reservasi
            </a>
            @include('admin.partials.logout_button')
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Reservasi</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
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
                            <td>{{ $reservation->start_time?->format('Y-m-d H:i') }}</td>
                            <td>{{ $reservation->end_time?->format('Y-m-d H:i') }}</td>
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
                                <a href="{{ route('admin.reservations.edit', $reservation->id) }}"
                                    class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i> Ubah
                                </a>
                                <form action="{{ route('admin.reservations.destroy', $reservation->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fas fa-times"></i> Batalkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data reservasi</td>
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
