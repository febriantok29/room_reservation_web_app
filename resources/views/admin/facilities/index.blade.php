@extends('adminlte::page')

@section('title', 'Fasilitas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: .75rem;">
        <div>
            <h1 class="m-0">Fasilitas</h1>
            <div class="page-subtitle">Kelola master fasilitas ruangan yang dipakai di seluruh reservasi.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-plus"></i> Tambah Fasilitas
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Daftar Fasilitas</h3>
            <div class="card-tools">
                <form action="{{ route('admin.facilities') }}" method="GET" class="input-group input-group-sm"
                    style="width: 260px;">
                    <input type="text" name="q" class="form-control float-right" placeholder="Cari nama/slug"
                        value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body border-bottom py-2 text-sm text-muted">
            Menampilkan {{ $facilities->count() }} dari {{ $facilities->total() }} fasilitas.
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Slug</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facilities as $facility)
                        <tr>
                            <td>{{ $facility->name }}</td>
                            <td>{{ $facility->slug }}</td>
                            <td>
                                <div class="table-action-group">
                                    <a href="{{ route('admin.facilities.edit', $facility->id) }}"
                                        class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i> Ubah
                                    </a>
                                    <form action="{{ route('admin.facilities.destroy', $facility->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin ingin menghapus fasilitas ini?')">
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
                            <td colspan="3" class="empty-state-cell">
                                <div class="empty-icon"><i class="fas fa-plug"></i></div>
                                <div class="empty-title">Belum ada data fasilitas</div>
                                <div class="empty-desc">Tambahkan fasilitas agar dapat dipilih saat pengelolaan ruangan.
                                </div>
                                <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Tambah Fasilitas
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $facilities->links() }}
        </div>
    </div>
@stop
