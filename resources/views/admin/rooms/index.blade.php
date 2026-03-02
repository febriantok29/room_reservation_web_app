@extends('adminlte::page')

@section('title', 'Ruangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Ruangan</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-plus"></i> Tambah Ruangan
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Ruangan</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
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
                                <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i> Ubah
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus ruangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data ruangan</td>
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
