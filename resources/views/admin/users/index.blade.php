@extends('adminlte::page')

@section('title', 'Karyawan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Karyawan</h1>
            <div class="page-subtitle">Kelola akun karyawan dan admin sistem.</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Karyawan
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-header">
            <h3 class="card-title">Daftar Karyawan</h3>
        </div>

        <div class="card-body border-bottom py-3">
            <form action="{{ route('admin.users') }}" method="GET">
                <div class="row">
                    <div class="col-lg-6 col-md-5 mb-2 mb-md-0">
                        <input type="text" name="q" class="form-control form-control-sm"
                            placeholder="Cari nama, email, atau no. induk..." value="{{ $searchQuery }}">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                        <select name="division_id" class="form-control form-control-sm">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ $divisionFilter === $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body border-bottom py-2 text-sm text-muted">
            Menampilkan {{ $users->count() }} dari {{ $users->total() }} karyawan.
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:150px;">No. Induk</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th style="width:160px;">Divisi</th>
                        <th class="text-center" style="width:90px;">Peran</th>
                        <th class="text-center" style="width:90px;">Status</th>
                        <th style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="text-monospace small">{{ $user->employee_id }}</td>
                            <td class="font-weight-bold">{{ $user->full_name }}</td>
                            <td class="text-muted small">{{ $user->email }}</td>
                            <td>
                                @if ($user->division)
                                    <span class="badge badge-primary">{{ $user->division->code }}</span>
                                    <span class="small">{{ $user->division->name }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $user->is_admin ? 'info' : 'secondary' }}">
                                    {{ $user->is_admin ? 'Admin' : 'Karyawan' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex" style="gap:.4rem;">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('Hapus karyawan {{ $user->full_name }}? Riwayat reservasi tetap tersimpan.')">
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
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@stop
