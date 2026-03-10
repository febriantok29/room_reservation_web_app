@extends('adminlte::page')

@section('title', 'Ruangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: .75rem;">
        <div>
            <h1 class="m-0">Ruangan</h1>
            <div class="page-subtitle">Kelola data ruangan, kapasitas, fasilitas, dan status maintenance.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-plus"></i> Tambah Ruangan
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Daftar Ruangan</h3>
        </div>

        {{-- Filter Section --}}
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
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Lantai</th>
                        <th>Kapasitas</th>
                        <th>Fasilitas</th>
                        <th>Maintenance</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                        <tr>
                            <td>{{ $room->name }}</td>
                            <td>Lantai {{ $room->floor }}</td>
                            <td>{{ $room->capacity }}</td>
                            <td>
                                @if ($room->facilities->isNotEmpty())
                                    {{ $room->facilities->pluck('name')->implode(', ') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($room->is_maintenance)
                                    <span class="badge bg-danger">YA</span>
                                @else
                                    <span class="badge bg-success">TIDAK</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-action-group">
                                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                        data-target="#roomDetailModal" data-room-id="{{ $room->id }}"
                                        data-room-name="{{ $room->name }}" data-room-floor="{{ $room->floor }}"
                                        data-room-capacity="{{ $room->capacity }}"
                                        data-room-description="{{ $room->description ?? '-' }}"
                                        data-room-facilities="{{ $room->facilities->pluck('name')->implode(', ') ?: '-' }}"
                                        data-room-maintenance="{{ $room->is_maintenance ? 'Ya' : 'Tidak' }}"
                                        data-room-created="{{ $room->created_at?->format('d M Y H:i') ?? '-' }}"
                                        data-room-updated="{{ $room->updated_at?->format('d M Y H:i') ?? '-' }}">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state-cell">
                                <div class="empty-icon"><i class="fas fa-door-open"></i></div>
                                <div class="empty-title">Belum ada data ruangan</div>
                                <div class="empty-desc">Tambahkan ruangan pertama untuk mulai menerima reservasi.</div>
                                <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambah Ruangan
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $rooms->links() }}
        </div>
    </div>

    {{-- Room Detail Modal --}}
    <div class="modal fade" id="roomDetailModal" tabindex="-1" role="dialog" aria-labelledby="roomDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="roomDetailModalLabel">
                        <i class="fas fa-door-open"></i> Detail Ruangan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">ID:</td>
                                    <td class="font-weight-bold" id="modal-room-id">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nama:</td>
                                    <td class="font-weight-bold" id="modal-room-name">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lantai:</td>
                                    <td id="modal-room-floor">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Kapasitas:</td>
                                    <td id="modal-room-capacity">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Maintenance:</td>
                                    <td id="modal-room-maintenance">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Dibuat:</td>
                                    <td id="modal-room-created">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Diupdate:</td>
                                    <td id="modal-room-updated">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="font-weight-bold">Deskripsi:</h6>
                            <p id="modal-room-description" class="text-muted">-</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="font-weight-bold">Fasilitas:</h6>
                            <p id="modal-room-facilities" class="text-muted">-</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#roomDetailModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);

                modal.find('#modal-room-id').text(button.data('room-id'));
                modal.find('#modal-room-name').text(button.data('room-name'));
                modal.find('#modal-room-floor').text('Lantai ' + button.data('room-floor'));
                modal.find('#modal-room-capacity').text(button.data('room-capacity') + ' orang');
                modal.find('#modal-room-description').text(button.data('room-description'));
                modal.find('#modal-room-facilities').text(button.data('room-facilities'));
                modal.find('#modal-room-maintenance').text(button.data('room-maintenance'));
                modal.find('#modal-room-created').text(button.data('room-created'));
                modal.find('#modal-room-updated').text(button.data('room-updated'));
            });
        });
    </script>
@stop

@include('admin.partials.timezone_detector')
