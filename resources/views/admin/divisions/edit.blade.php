@extends('adminlte::page')

@section('title', 'Edit Divisi')

@section('content_header')
    <div>
        <h1 class="m-0">Edit Divisi</h1>
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
            <h3 class="card-title">
                Edit: <span class="badge badge-primary">{{ $division->code }}</span>
                {{ $division->name }}
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.divisions.update', $division) }}" method="POST">
                @csrf @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Divisi <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $division->name) }}"
                        required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code">Kode Divisi <span class="text-danger">*</span></label>
                    <input type="text" id="code" name="code"
                        class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $division->code) }}"
                        maxlength="10" style="text-transform:uppercase;" required>
                    <small class="form-text text-muted">
                        Perubahan kode akan mempengaruhi format ID karyawan baru. ID karyawan yang sudah ada tidak berubah.
                    </small>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                        rows="3">{{ old('description', $division->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Perbarui
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
