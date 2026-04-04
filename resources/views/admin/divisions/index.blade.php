@extends('adminlte::page')

@section('title', 'Divisi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Divisi</h1>
            <div class="page-subtitle">Kelola master divisi yang digunakan dalam pengelolaan data pengguna.</div>
        </div>
        <a href="{{ route('admin.divisions.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Divisi
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Daftar Divisi</h3>
        </div>

        <div class="card-body border-bottom py-3">
            <form action="{{ route('admin.divisions') }}" method="GET">
                <div class="row">
                    <div class="col-lg-9 col-md-8 mb-2 mb-md-0">
                        <input type="text" name="q" class="form-control form-control-sm"
                            placeholder="Cari nama atau kode divisi..." value="{{ $searchQuery }}">
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
            Menampilkan {{ $divisions->count() }} divisi.
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:100px;">ID</th>
                        <th style="width:80px;">Kode</th>
                        <th>Nama Divisi</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width:100px;">Pengguna</th>
                        <th style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisions as $division)
                        <tr>
                            <td class="text-monospace small">{{ $division->id }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $division->code }}</span>
                            </td>
                            <td class="font-weight-bold">{{ $division->name }}</td>
                            <td class="text-muted small">{{ $division->description ?? '-' }}</td>
                            <td class="text-center">{{ $division->users_count }}</td>
                            <td>
                                <div class="d-flex" style="gap:.4rem;">
                                    <a href="{{ route('admin.divisions.edit', $division) }}" class="btn btn-warning btn-sm"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.divisions.destroy', $division) }}" method="POST"
                                        onsubmit="return confirm('Hapus divisi {{ $division->name }}? Pastikan tidak ada pengguna aktif di divisi ini.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                Belum ada data divisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
