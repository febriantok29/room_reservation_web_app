@extends('adminlte::page')

@section('title', 'Dasbor Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Dashboard Admin</h1>
        @include('admin.partials.logout_button')
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total_rooms'] }}</h3>
                    <p>Total Ruangan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['total_users'] }}</h3>
                    <p>Total Pengguna</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['pending_reservations'] }}</h3>
                    <p>Reservasi Menunggu</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $summary['approved_reservations'] }}</h3>
                    <p>Reservasi Disetujui</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reservasi Terbaru</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Ruangan</th>
                        <th>Mulai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestReservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user?->full_name ?? '-' }}</td>
                            <td>{{ $reservation->room?->name ?? '-' }}</td>
                            <td>{{ $reservation->start_time?->format('Y-m-d H:i') }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data reservasi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
