@extends('adminlte::page')

@section('title', 'Tambah Karyawan')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Karyawan</h1>
        <div class="page-subtitle">
            <a href="{{ route('admin.users') }}" class="text-muted">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar karyawan
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin" style="max-width:600px;">
        <div class="card-header py-3">
            <h3 class="card-title">Form Karyawan Baru</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST" data-submit-guard
                data-loading-text="Memproses...">
                @csrf

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="first_name">Nama Depan <span class="text-danger">*</span></label>
                        <input type="text" id="first_name" name="first_name"
                            class="form-control @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name') }}" maxlength="50" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="last_name">Nama Belakang <span class="text-danger">*</span></label>
                        <input type="text" id="last_name" name="last_name"
                            class="form-control @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name') }}" maxlength="50" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" maxlength="100" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="division_id">Divisi</label>
                    <select id="division_id" name="division_id"
                        class="form-control @error('division_id') is-invalid @enderror">
                        <option value="">— tanpa divisi (khusus admin) —</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id') === $division->id ? 'selected' : '' }}>
                                {{ $division->name }} ({{ $division->code }})
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        No. Induk dibuat otomatis dari kode divisi (cth. IT-2026-00011) atau ADM-2026-XX untuk admin tanpa divisi.
                    </small>
                    @error('division_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Tanggal Lahir</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                        class="form-control @error('date_of_birth') is-invalid @enderror"
                        value="{{ old('date_of_birth') }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="is_admin" name="is_admin" value="1" class="custom-control-input"
                            {{ old('is_admin') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_admin">Jadikan admin (akses web admin)</label>
                    </div>
                </div>

                <div class="alert alert-info py-2 small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Password awal karyawan baru: <strong>User@123</strong> — sampaikan ke karyawan dan sarankan tidak dibagikan.
                </div>

                <div class="d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop
