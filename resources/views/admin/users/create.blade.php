@extends('adminlte::page')

@section('title', 'Tambah Karyawan')

<x-admin.page-header title="Tambah Karyawan" subtitle="Kembali ke daftar karyawan" back-url="{{ route('admin.users') }}" />

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.users.store') }}">
        <x-form.section title="Data Karyawan">
            <x-form.row>
                <x-form.field name="first_name" label="Nama Depan" required col-class="col-md-6" />
                <x-form.field name="last_name" label="Nama Belakang" required col-class="col-md-6" />
            </x-form.row>
            <x-form.field name="email" label="Email" type="email" required col-class="col-md-6" />
            <x-form.field name="division_id" label="Divisi" type="select" col-class="col-md-6"
                hint="Karyawan non-admin wajib memiliki divisi."
                :options="$divisions->pluck('name', 'id')->prepend('— tanpa divisi (khusus admin) —', '')" />
            <x-form.field name="date_of_birth" label="Tanggal Lahir" type="date" required col-class="col-md-6"
                hint="Digunakan untuk membuat password awal karyawan." />
            <x-form.row>
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" id="is_admin" name="is_admin" value="1" class="custom-control-input"
                            {{ old('is_admin') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_admin">Jadikan admin (akses web admin)</label>
                    </div>
                </div>
            </x-form.row>
        </x-form.section>

        <div class="alert alert-info py-2 small">
            <i class="fas fa-info-circle mr-1"></i>
            Password awal dibuat otomatis dari No. Induk + tanggal lahir dan hanya ditampilkan sekali setelah
            disimpan. Karyawan wajib mengganti password saat login pertama.
        </div>

        <x-form.actions back-url="{{ route('admin.users') }}" />
    </x-form.card>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
@endpush
