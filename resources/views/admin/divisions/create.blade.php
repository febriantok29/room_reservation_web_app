@extends('adminlte::page')

@section('title', 'Tambah Divisi')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Divisi</h1>
        <div class="page-subtitle">
            <a href="{{ route('admin.divisions') }}" class="text-muted">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar divisi
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-admin" style="max-width:600px;">
        <div class="card-header py-3">
            <h3 class="card-title">Form Divisi Baru</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.divisions.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Divisi <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                        placeholder="cth. Divisi Teknologi Informasi" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code">Kode Divisi <span class="text-danger">*</span></label>
                    <input type="text" id="code" name="code"
                        class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}"
                        placeholder="cth. TI" maxlength="10" style="text-transform:uppercase;" required>
                    <small class="form-text text-muted">
                        Kode singkat (max 10 karakter) yang akan muncul di ID karyawan. Contoh: OPS, KNP, HRD.
                    </small>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                        rows="3" placeholder="Deskripsi singkat tentang divisi ini (opsional)...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="{{ route('admin.divisions') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.getElementById('code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
@stop
