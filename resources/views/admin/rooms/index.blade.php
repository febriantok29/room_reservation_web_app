@extends('adminlte::page')

@section('title', 'Ruangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: .75rem;">
        <div>
            <h1 class="m-0">Ruangan</h1>
            <div class="page-subtitle">Kelola data ruangan, kapasitas, fasilitas, dan status maintenance.</div>
        </div>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Ruangan
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Daftar Ruangan</h3>
        </div>

        {{-- Filter --}}
        <div class="card-body border-bottom">
            <form action="{{ route('admin.rooms') }}" method="GET">
                <div class="row">
                    <div class="col-lg-6 col-md-7">
                        <div class="form-group mb-0">
                            <input type="text" name="q" class="form-control form-control-sm"
                                placeholder="Cari nama ruangan, lantai, atau fasilitas..." value="{{ $searchQuery ?? '' }}">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <div class="form-group mb-0">
                            <select name="maintenance" class="form-control form-control-sm">
                                <option value="">Semua Ruangan</option>
                                <option value="0" @selected(request('maintenance') === '0')>Tidak Maintenance</option>
                                <option value="1" @selected(request('maintenance') === '1')>Sedang Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body border-bottom py-2 text-sm text-muted">
            Menampilkan {{ $rooms->count() }} dari {{ $rooms->total() }} ruangan.
        </div>

        {{-- Card grid --}}
        <div class="card-body">
            <div class="row">
                @forelse ($rooms as $room)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3 d-flex">
                        <div class="card w-100 border-0 shadow-sm {{ $room->is_maintenance ? 'border-top border-warning' : '' }}"
                            style="border-radius:.5rem;overflow:hidden;">

                            {{-- Thumbnail --}}
                            <div style="position:relative;height:160px;overflow:hidden;background:#e9ecef;">
                                <img src="{{ $room->image_url ?? asset('images/not_available.jpg') }}"
                                    alt="{{ $room->name }}" class="w-100 h-100"
                                    style="object-fit:cover;{{ $room->image_url ? '' : 'opacity:.6;' }}">
                                {{-- Status badge overlay --}}
                                @if ($room->is_maintenance)
                                    <span class="badge badge-warning"
                                        style="position:absolute;top:8px;right:8px;font-size:.7rem;">
                                        <i class="fas fa-wrench mr-1"></i>Maintenance
                                    </span>
                                @else
                                    <span class="badge badge-success"
                                        style="position:absolute;top:8px;right:8px;font-size:.7rem;">
                                        <i class="fas fa-check mr-1"></i>Tersedia
                                    </span>
                                @endif
                            </div>

                            {{-- Body --}}
                            <div class="card-body py-2 px-3">
                                <h6 class="font-weight-bold mb-1 text-truncate" title="{{ $room->name }}">
                                    {{ $room->name }}
                                </h6>
                                <div class="text-muted mb-2" style="font-size:.78rem;">
                                    <i class="fas fa-layer-group mr-1"></i>Lantai {{ $room->floor }}
                                    &nbsp;&middot;&nbsp;
                                    <i class="fas fa-users mr-1"></i>{{ $room->capacity }} orang
                                </div>
                                <div>
                                    @forelse ($room->facilities->take(3) as $fac)
                                        <span class="badge badge-light border text-secondary mb-1"
                                            style="font-size:.68rem;">{{ $fac->name }}</span>
                                    @empty
                                        <span class="text-muted" style="font-size:.75rem;">Tidak ada fasilitas</span>
                                    @endforelse
                                    @if ($room->facilities->count() > 3)
                                        <span class="badge badge-secondary mb-1" style="font-size:.68rem;"
                                            title="{{ $room->facilities->skip(3)->pluck('name')->implode(', ') }}">
                                            +{{ $room->facilities->count() - 3 }} lagi
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer actions --}}
                            <div class="card-footer bg-white border-top pt-2 pb-2 px-3">
                                <div class="table-action-group">
                                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                        data-target="#roomDetailModal" data-room-id="{{ $room->id }}"
                                        data-room-name="{{ $room->name }}" data-room-floor="{{ $room->floor }}"
                                        data-room-capacity="{{ $room->capacity }}"
                                        data-room-description="{{ $room->description ?? '-' }}"
                                        data-room-facilities="{{ $room->facilities->pluck('name')->implode(', ') ?: '-' }}"
                                        data-room-maintenance="{{ $room->is_maintenance ? 'Ya' : 'Tidak' }}"
                                        data-room-created="{{ $room->created_at?->format('d M Y H:i') ?? '-' }}"
                                        data-room-updated="{{ $room->updated_at?->format('d M Y H:i') ?? '-' }}"
                                        data-room-image-url="{{ $room->image_url ?? '' }}"
                                        data-room-edit-url="{{ route('admin.rooms.edit', $room->id) }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i> Ubah
                                    </a>
                                    <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state-cell" style="border:none;">
                            <div class="empty-icon"><i class="fas fa-door-open"></i></div>
                            <div class="empty-title">Belum ada data ruangan</div>
                            <div class="empty-desc">Tambahkan ruangan pertama untuk mulai menerima reservasi.</div>
                            <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Ruangan
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card-footer clearfix">
            {{ $rooms->links() }}
        </div>
    </div>

    {{-- ── Room Detail Modal ────────────────────────────────────────── --}}
    <div class="modal fade" id="roomDetailModal" tabindex="-1" role="dialog" aria-labelledby="roomDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius:.5rem;overflow:hidden;">

                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title font-weight-bold" id="modal-room-name">—</h5>
                        <small class="text-muted" id="modal-room-id-display"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body pt-2">
                    {{-- Top: image (left) + key stats (right) --}}
                    <div class="row no-gutters mb-3">
                        <div class="col-md-5 pr-md-3">
                            <div style="height:220px;overflow:hidden;border-radius:.4rem;background:#e9ecef;">
                                <img id="modal-room-image" src="" alt="Foto Ruangan" class="w-100 h-100"
                                    style="object-fit:cover;">
                            </div>
                        </div>
                        <div class="col-md-7 pt-3 pt-md-0 pl-md-1">
                            <div class="mb-2">
                                <span id="modal-room-maintenance-badge" class="badge px-2 py-1"
                                    style="font-size:.8rem;"></span>
                            </div>
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted pl-0" style="width:42%;">Lantai</td>
                                        <td class="font-weight-semibold" id="modal-room-floor">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-0">Kapasitas</td>
                                        <td class="font-weight-semibold" id="modal-room-capacity">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-0">Dibuat</td>
                                        <td id="modal-room-created">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-0">Diperbarui</td>
                                        <td id="modal-room-updated">—</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="mb-2">
                        <strong class="small text-uppercase text-muted" style="letter-spacing:.05em;">Fasilitas</strong>
                        <p class="mb-0 mt-1" id="modal-room-facilities">—</p>
                    </div>
                    <div>
                        <strong class="small text-uppercase text-muted" style="letter-spacing:.05em;">Deskripsi</strong>
                        <p class="mb-0 mt-1 text-muted" id="modal-room-description">—</p>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <a id="modal-room-edit-link" href="#" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit mr-1"></i> Ubah Ruangan
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var placeholder = '{{ asset('images/not_available.jpg') }}';

            $('#roomDetailModal').on('show.bs.modal', function(event) {
                var btn = $(event.relatedTarget);
                var modal = $(this);

                modal.find('#modal-room-name').text(btn.data('room-name'));
                modal.find('#modal-room-id-display').text('ID: ' + btn.data('room-id'));
                modal.find('#modal-room-floor').text('Lantai ' + btn.data('room-floor'));
                modal.find('#modal-room-capacity').text(btn.data('room-capacity') + ' orang');
                modal.find('#modal-room-description').text(btn.data('room-description'));
                modal.find('#modal-room-facilities').text(btn.data('room-facilities'));
                modal.find('#modal-room-created').text(btn.data('room-created'));
                modal.find('#modal-room-updated').text(btn.data('room-updated'));

                var isMaintenance = btn.data('room-maintenance') === 'Ya';
                modal.find('#modal-room-maintenance-badge')
                    .text(isMaintenance ? 'Maintenance' : 'Tersedia')
                    .removeClass('badge-success badge-warning')
                    .addClass(isMaintenance ? 'badge-warning' : 'badge-success');

                modal.find('#modal-room-image')
                    .attr('src', btn.data('room-image-url') || placeholder);

                modal.find('#modal-room-edit-link')
                    .attr('href', btn.data('room-edit-url'));
            });
        });
    </script>
@stop

@include('admin.partials.timezone_detector')
