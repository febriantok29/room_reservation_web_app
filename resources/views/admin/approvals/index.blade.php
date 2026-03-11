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
        </div>

        {{-- Filter Section --}}
        <div class="card-body border-bottom">
            <form action="{{ route('admin.approvals') }}" method="GET">
                <div class="row">
                    <div class="col-lg-9 col-md-8">
                        <div class="form-group mb-0">
                            <input type="text" name="q" class="form-control form-control-sm"
                                placeholder="Cari ID reservasi, nama pengguna, atau ruangan..."
                                value="{{ $searchQuery ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
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
                                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                        data-target="#reservationDetailModal" data-id="{{ $reservation->id }}"
                                        data-user="{{ $reservation->user?->full_name ?? '-' }}"
                                        data-room="{{ $reservation->room?->name ?? '-' }}"
                                        data-start="{{ $reservation->start_time_label }}"
                                        data-end="{{ $reservation->end_time_label }}"
                                        data-visitors="{{ $reservation->visitor_count }}"
                                        data-purpose="{{ $reservation->purpose ?? '-' }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
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
    {{-- Reservation Detail Modal --}}
    <div class="modal fade" id="reservationDetailModal" tabindex="-1" role="dialog"
        aria-labelledby="reservationDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservationDetailModalLabel">Detail Reservasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light" style="width:38%">ID Reservasi</th>
                                <td id="modal-detail-id"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Pengguna</th>
                                <td id="modal-detail-user"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ruangan</th>
                                <td id="modal-detail-room"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Waktu Mulai</th>
                                <td id="modal-detail-start"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Waktu Selesai</th>
                                <td id="modal-detail-end"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Jml. Pengunjung</th>
                                <td id="modal-detail-visitors"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Keperluan</th>
                                <td id="modal-detail-purpose"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $('#reservationDetailModal').on('show.bs.modal', function(event) {
            var btn = $(event.relatedTarget);
            $('#modal-detail-id').text(btn.data('id'));
            $('#modal-detail-user').text(btn.data('user'));
            $('#modal-detail-room').text(btn.data('room'));
            $('#modal-detail-start').text(btn.data('start'));
            $('#modal-detail-end').text(btn.data('end'));
            $('#modal-detail-visitors').text(btn.data('visitors'));
            $('#modal-detail-purpose').text(btn.data('purpose'));
        });
    </script>
@endsection

@include('admin.partials.timezone_detector')
