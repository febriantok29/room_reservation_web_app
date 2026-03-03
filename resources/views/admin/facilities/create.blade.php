@extends('adminlte::page')

@section('title', 'Tambah Fasilitas')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Fasilitas</h1>
        <div class="page-subtitle">Tambahkan fasilitas baru agar bisa dipakai pada data ruangan.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.facilities.store') }}">
        <x-form.section title="Informasi Fasilitas">
            <x-form.row>
                <x-form.field name="name" label="Nama Fasilitas" type="text" :value="old('name')"
                    hint="Contoh: Proyektor, Whiteboard, Video Conference." required />
            </x-form.row>
        </x-form.section>

        <x-form.actions back-url="{{ route('admin.facilities') }}" submit-text="Simpan" />
    </x-form.card>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop
