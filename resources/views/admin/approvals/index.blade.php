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

    {{-- Filter Bar --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form action="{{ route('admin.approvals') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-lg-9 col-md-8 mb-2 mb-lg-0">
                        <input type="text" name="q" class="form-control form-control-sm"
                            placeholder="Cari ID reservasi, nama pengguna, atau ruangan..."
                            value="{{ $searchQuery ?? '' }}">
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            Menampilkan {{ $pendingReservations->count() }} dari {{ $pendingReservations->total() }} antrian.
        </div>
    </div>

    {{-- Approval Cards --}}
    <div class="row">
        @forelse ($pendingReservations as $reservation)
            <div class="col-xl-6 col-12 mb-3">
                <div class="card card-admin h-100" style="border-left:4px solid #ffc107;">

                    {{-- Card Header --}}
                    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted small font-weight-bold">
                            <i class="fas fa-hashtag mr-1" style="font-size:.7rem;"></i>{{ $reservation->id }}
                        </span>
                        <span class="badge badge-warning">
                            <i class="fas fa-clock mr-1"></i>MENUNGGU
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body py-3 px-3">

                        {{-- Room --}}
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-door-open mr-2"
                                style="color:#ffc107;width:16px;flex-shrink:0;font-size:.95rem;"></i>
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
    <span class="text-muted">({{ $reservation->user?->division?->name ?? '-' }})</span>
    @if($reservation->with_snack || $reservation->with_lunch)
        <span class="ml-2">
            @if($reservation->with_snack)<span class="badge badge-warning"><i class="fas fa-cookie-bite"></i> Snack</span> @endif
            @if($reservation->with_lunch)<span class="badge badge-info"><i class="fas fa-utensils"></i> Makan Siang</span> @endif
        </span>
    @endif
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
                        <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                            data-target="#reservationDetailModal" data-id="{{ $reservation->id }}"
                            data-user="{{ $reservation->user?->full_name ?? '-' }}"
                            data-division="{{ $reservation->user?->division?->name ?? '-' }}"
                            data-with-snack="{{ $reservation->with_snack ? 1 : 0 }}"
                            data-with-lunch="{{ $reservation->with_lunch ? 1 : 0 }}"
                            data-room="{{ $reservation->room?->name ?? '-' }}"
                            data-start="{{ $reservation->start_time_label }}"
                            data-end="{{ $reservation->end_time_label }}"
                            data-visitors="{{ $reservation->visitor_count }}"
                            data-purpose="{{ $reservation->purpose ?? '-' }}">
                            <i class="fas fa-eye"></i> Detail
                        </button>

                        <button type="button" class="btn btn-success btn-xs" data-toggle="modal"
                            data-target="#approveModal" data-id="{{ $reservation->id }}"
                            data-room-id="{{ $reservation->room_id }}"
                            data-approve-url="{{ route('admin.approvals.approve', $reservation->id) }}">
                            <i class="fas fa-check"></i> Setujui
                        </button>

                        <form action="{{ route('admin.approvals.reject', $reservation->id) }}" method="POST"
                            class="d-inline" onsubmit="return confirm('Yakin ingin menolak reservasi ini?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-xs">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-admin">
                    <div class="card-body">
                        <div class="empty-state-cell">
                            <div class="empty-icon"><i class="fas fa-clipboard-check"></i></div>
                            <div class="empty-title">Tidak ada antrian persetujuan</div>
                            <div class="empty-desc">Semua reservasi sudah diproses atau belum ada permintaan baru.</div>
                            <a href="{{ route('admin.reservations') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list mr-1"></i> Lihat Semua Reservasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($pendingReservations->hasPages())
        <div class="d-flex justify-content-center mt-1">
            {{ $pendingReservations->links() }}
        </div>
    @endif

    {{-- Approve with Room Reassignment Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#28a745;color:white;">
                    <h5 class="modal-title" id="approveModalLabel">
                        <i class="fas fa-check-circle mr-2"></i>Setujui Reservasi
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="approve-modal-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3 text-sm">
                            Menyetujui reservasi <strong id="approve-modal-id"></strong>.
                        </p>
                        <div class="form-group mb-0">
                            <label for="approve-room-select" class="font-weight-semibold">
                                Ruangan
                                <span class="text-muted font-weight-normal small">(opsional: pindahkan ke ruangan
                                    lain)</span>
                            </label>
                            <select name="room_id" id="approve-room-select" class="form-control">
                                <option value="">— Pertahankan ruangan asli —</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" data-capacity="{{ $room->capacity }}">
                                        {{ $room->name }} — Lantai {{ $room->floor }} · Kap. {{ $room->capacity }}
                                        orang
                                        {{ $room->is_maintenance ? '(Maintenance)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Pilih ruangan lain jika perlu dipindah. CSP akan divalidasi ulang.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check mr-1"></i> Konfirmasi Setujui
                        </button>
                    </div>
                </form>
            </div>
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
                                <th class="bg-light">Divisi</th>
                                <td id="modal-detail-division"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Konsumsi</th>
                                <td id="modal-detail-consumption"></td>
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

@push('js')
    <script>
        $('#reservationDetailModal').on('show.bs.modal', function(event) {
            var btn = $(event.relatedTarget);
            $('#modal-detail-id').text(btn.data('id'));
            $('#modal-detail-user').text(btn.data('user'));
            $('#modal-detail-division').text(btn.data('division'));
            var snack = btn.attr('data-with-snack') === '1';
            var lunch = btn.attr('data-with-lunch') === '1';
            var consumptionHtml = '-';
            if (snack || lunch) {
                consumptionHtml = '';
                if (snack) consumptionHtml += '<span class="badge badge-warning"><i class="fas fa-cookie-bite"></i> Snack</span> ';
                if (lunch) consumptionHtml += '<span class="badge badge-info"><i class="fas fa-utensils"></i> Makan Siang</span>';
            }
            $('#modal-detail-consumption').html(consumptionHtml);
            $('#modal-detail-room').text(btn.data('room'));
            $('#modal-detail-start').text(btn.data('start'));
            $('#modal-detail-end').text(btn.data('end'));
            $('#modal-detail-visitors').text(btn.data('visitors'));
            $('#modal-detail-purpose').text(btn.data('purpose'));
        });

        $('#approveModal').on('show.bs.modal', function(event) {
            var btn = $(event.relatedTarget);
            $('#approve-modal-id').text(btn.data('id'));
            $('#approve-modal-form').attr('action', btn.data('approve-url'));
            $('#approve-room-select').val(btn.data('room-id'));
        });
    </script>
@endpush

@include('admin.partials.timezone_detector')
