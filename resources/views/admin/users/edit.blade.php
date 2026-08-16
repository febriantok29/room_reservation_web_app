@extends('adminlte::page')

@section('title', 'Edit Karyawan')

<x-admin.page-header title="Edit Karyawan" subtitle="Kembali ke daftar karyawan" back-url="{{ route('admin.users') }}" />

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.users.update', $user) }}" method="PUT">
        <div class="mb-3">
            <span class="h5">{{ $user->full_name }}</span>
            <span class="badge badge-light text-monospace ml-1">{{ $user->employee_id }}</span>
        </div>

        <x-form.section title="Data Karyawan">
            <x-form.row>
                <x-form.field name="first_name" label="Nama Depan" required col-class="col-md-6" :value="$user->first_name" />
                <x-form.field name="last_name" label="Nama Belakang" required col-class="col-md-6" :value="$user->last_name" />
            </x-form.row>
            <x-form.field name="email" label="Email" type="email" required col-class="col-md-6" :value="$user->email" />
            <x-form.field name="division_id" label="Divisi" type="select" col-class="col-md-6"
                hint="No. Induk tidak berubah walau divisi diganti (ID historis)."
                :options="$divisions->pluck('name', 'id')->prepend('— tanpa divisi —', '')"
                :value="$user->division_id" />
            <x-form.field name="date_of_birth" label="Tanggal Lahir" type="date" required col-class="col-md-6"
                :value="$user->date_of_birth?->format('Y-m-d')" />
            <x-form.row>
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox mt-2">
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
            </x-form.row>
        </x-form.section>

        <x-form.actions back-url="{{ route('admin.users') }}" submit-text="Simpan Perubahan" />
    </x-form.card>

    <div class="card card-admin" style="max-width:600px;">
        <div class="card-header py-3">
            <h3 class="card-title">Reset Password</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Password akan direset ke password awal (No. Induk + tanggal lahir) dan ditampilkan satu kali.
                Karyawan wajib mengganti password saat login berikutnya. Gunakan bila karyawan lupa password.
            </p>
            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
                data-confirm-delete="Reset password {{ $user->full_name }} ke password awal?"
                data-submit-guard data-loading-text="Memproses...">
                @csrf
                <button type="submit" class="btn btn-outline-warning">
                    <i class="fas fa-key mr-1"></i> Reset Password
                </button>
            </form>
        </div>
    </div>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
    @include('admin.partials.confirm_delete')
@endpush
