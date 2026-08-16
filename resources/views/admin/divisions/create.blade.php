@extends('adminlte::page')

@section('title', 'Tambah Divisi')

<x-admin.page-header title="Tambah Divisi" subtitle="Kembali ke daftar divisi" back-url="{{ route('admin.divisions') }}" />

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.divisions.store') }}">
        <x-form.field name="name" label="Nama Divisi" required placeholder="cth. Divisi Teknologi Informasi" />
        <x-form.field name="code" label="Kode Divisi" required
            hint="Kode singkat (max 10 karakter) yang akan muncul di ID karyawan. Contoh: OPS, KNP, HRD.">
            <input type="text" id="code" name="code"
                class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}"
                placeholder="cth. TI" maxlength="10" style="text-transform:uppercase;" required>
        </x-form.field>
        <x-form.field name="description" label="Deskripsi" type="textarea" rows="3"
            placeholder="Deskripsi singkat tentang divisi ini (opsional)..." />
        <x-form.actions back-url="{{ route('admin.divisions') }}" />
    </x-form.card>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
    <script>
        document.getElementById('code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
@endpush
