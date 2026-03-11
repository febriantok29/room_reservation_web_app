@extends('adminlte::page')

@section('title', 'Tambah Ruangan')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Ruangan</h1>
        <div class="page-subtitle">Isi informasi ruangan baru beserta fasilitas pendukung yang tersedia.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.rooms.store') }}">
        <x-form.section title="Informasi Dasar">
            <x-form.row>
                <x-form.field name="name" label="Nama Ruangan" type="text" col-class="col-lg-4 col-md-6" required />

                <x-form.field name="floor" label="Lantai" type="number" col-class="col-lg-4 col-md-6" hint="Contoh: 1"
                    min="1" max="99" required />

                <x-form.field name="capacity" label="Kapasitas (Orang)" type="number" :value="old('capacity', 1)"
                    col-class="col-lg-4 col-md-6" min="1" required />
            </x-form.row>

            <x-form.field name="description" label="Deskripsi" type="textarea" col-class="" rows="3"
                hint="Opsional: Jelaskan kegunaan atau fitur khusus ruangan ini." />
        </x-form.section>

        <x-form.section title="Fasilitas & Status">
            <x-form.row>
                <div class="col-lg-6">
                    @include('admin.rooms.partials.facility_chip_input_field', [
                        'selectedIds' => old('facility_ids', []),
                    ])
                </div>
            </x-form.row>

            <div class="form-group form-check">
                <input type="checkbox" id="is_maintenance" name="is_maintenance" value="1" class="form-check-input"
                    @checked(old('is_maintenance'))>
                <label class="form-check-label" for="is_maintenance">Sedang maintenance</label>
            </div>
        </x-form.section>

        <x-form.actions back-url="{{ route('admin.rooms') }}" submit-text="Simpan" />
    </x-form.card>
@stop

@section('css')
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop

@include('admin.partials.timezone_detector')
