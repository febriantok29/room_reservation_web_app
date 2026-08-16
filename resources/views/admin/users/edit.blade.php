@extends('adminlte::page')

@section('title', 'Edit Karyawan')

@section('content_header')
    <div>
        <h1 class="m-0">Edit Karyawan</h1>
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
            <h3 class="card-title">
                {{ $user->full_name }}
                <span class="badge badge-light text-monospace ml-1">{{ $user->employee_id }}</span>
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" data-submit-guard
                data-loading-text="Memproses...">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="first_name">Nama Depan <span class="text-danger">*</span></label>
                        <input type="text" id="first_name" name="first_name"
                            class="form-control @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name', $user->first_name) }}" maxlength="50" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="last_name">Nama Belakang <span class="text-danger">*</span></label>
                        <input type="text" id="last_name" name="last_name"
                            class="form-control @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name', $user->last_name) }}" maxlength="50" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}" maxlength="100" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="division_id">Divisi</label>
                    <select id="division_id" name="division_id"
                        class="form-control @error('division_id') is-invalid @enderror">
                        <option value="">— tanpa divisi —</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}"
                                {{ old('division_id', $user->division_id) === $division->id ? 'selected' : '' }}>
                                {{ $division->name }} ({{ $division->code }})
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">No. Induk tidak berubah walau divisi diganti (ID historis).</small>
                    @error('division_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Tanggal Lahir</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                        class="form-control @error('date_of_birth') is-invalid @enderror"
                        value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="is_admin" name="is_admin" value="1" class="custom-control-input"
                            {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_admin">Admin (akses web admin)</label>
                    </div>
                    <div class="custom-control custom-checkbox mt-1">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="custom-control-input"
                            {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif (boleh login)</label>
                    </div>
                    @error('is_active')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-admin" style="max-width:600px;">
        <div class="card-header py-3">
            <h3 class="card-title">Reset Password</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Password akan dikembalikan ke default <strong>User@123</strong>. Gunakan bila karyawan lupa password.
            </p>
            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                onsubmit="return confirm('Reset password {{ $user->full_name }} ke default?')"
                data-submit-guard data-loading-text="Memproses...">
                @csrf
                <button type="submit" class="btn btn-outline-warning">
                    <i class="fas fa-key mr-1"></i> Reset ke Password Default
                </button>
            </form>
        </div>
    </div>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop
