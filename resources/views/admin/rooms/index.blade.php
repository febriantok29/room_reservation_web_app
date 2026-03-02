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
            <div class="card-tools">
                <form action="{{ route('admin.rooms') }}" method="GET" class="input-group input-group-sm"
                    style="width: 300px;">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama, lokasi, fasilitas"
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
            Menampilkan {{ $rooms->count() }} dari {{ $rooms->total() }} ruangan.
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Lokasi</th>
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
                            <td>{{ $room->location }}</td>
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
@stop
