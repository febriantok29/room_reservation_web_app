@extends('adminlte::page')

@section('title', 'Edit Divisi')

<x-admin.page-header title="Edit Divisi" subtitle="Kembali ke daftar divisi" back-url="{{ route('admin.divisions') }}" />

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.divisions.update', $division) }}" method="PUT">
        <x-form.field name="name" label="Nama Divisi" required :value="$division->name" />
        <x-form.field name="code" label="Kode Divisi" required
            hint="Perubahan kode akan mempengaruhi format ID karyawan baru. ID karyawan yang sudah ada tidak berubah.">
            <input type="text" id="code" name="code"
                class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $division->code) }}"
                maxlength="10" style="text-transform:uppercase;" required>
        </x-form.field>
        <x-form.field name="description" label="Deskripsi" type="textarea" rows="3" :value="$division->description" />
        <x-form.actions back-url="{{ route('admin.divisions') }}" submit-text="Perbarui" />
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
