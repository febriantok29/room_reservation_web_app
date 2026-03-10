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
        </div>

        {{-- Filter Section --}}
        <div class="card-body border-bottom">
            <form action="{{ route('admin.facilities') }}" method="GET">
                <div class="row">
                    <div class="col-lg-9 col-md-8">
                        <div class="form-group mb-0">
                            <input type="text" name="q" class="form-control form-control-sm"
                                placeholder="Cari nama atau slug fasilitas..." value="{{ request('q') }}">
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
                                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal"
                                        data-target="#facilityDetailModal" data-facility-name="{{ $facility->name }}"
                                        data-facility-slug="{{ $facility->slug }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
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

    {{-- Facility Detail Modal --}}
    <div class="modal fade" id="facilityDetailModal" tabindex="-1" role="dialog"
        aria-labelledby="facilityDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="facilityDetailModalLabel">
                        <i class="fas fa-plug"></i> Detail Fasilitas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <div class="form-control-plaintext" id="facilityName">-</div>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <div class="form-control-plaintext" id="facilitySlug">-</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const facilityDetailModal = document.getElementById('facilityDetailModal');
            if (facilityDetailModal) {
                facilityDetailModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    document.getElementById('facilityName').textContent = button.getAttribute(
                        'data-facility-name');
                    document.getElementById('facilitySlug').textContent = button.getAttribute(
                        'data-facility-slug');
                });
            }
        });
    </script>
@stop

@include('admin.partials.timezone_detector')
