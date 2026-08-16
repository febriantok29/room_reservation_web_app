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

    <x-form.card action="{{ route('admin.rooms.store') }}" :multipart="true">
        <div class="row">

            {{-- ── Main fields (left) ─────────────────────────────── --}}
            <div class="col-lg-8 pr-lg-4">

                <x-form.section title="Informasi Dasar">
                    <x-form.row>
                        <x-form.field name="name" label="Nama Ruangan" type="text" col-class="col-lg-6 col-md-6"
                            required />
                        <x-form.field name="floor" label="Lantai" type="number" col-class="col-lg-3 col-md-3"
                            hint="1 – 99" min="1" max="99" required />
                        <x-form.field name="capacity" label="Kapasitas (Orang)" type="number" col-class="col-lg-3 col-md-3"
                            :value="old('capacity', 1)" min="1" required />
                    </x-form.row>
                    <x-form.field name="description" label="Deskripsi" type="textarea" col-class="" rows="3"
                        hint="Opsional: Jelaskan kegunaan atau fitur khusus ruangan ini." />
                </x-form.section>

                <x-form.section title="Fasilitas & Status">
                    @include('admin.rooms.partials.facility_chip_input_field', [
                        'selectedIds' => old('facility_ids', []),
                    ])
                    <div class="form-group form-check mt-3 mb-0">
                        <input type="checkbox" id="is_maintenance" name="is_maintenance" value="1"
                            class="form-check-input" @checked(old('is_maintenance'))>
                        <label class="form-check-label" for="is_maintenance">Sedang maintenance</label>
                    </div>
                </x-form.section>

            </div>

            {{-- ── Image sidebar (right) ──────────────────────────── --}}
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="card border-0 bg-light" style="position:sticky; top:1rem;">
                    <div class="card-body">
                        <div class="form-section-title mb-2">Foto Ruangan</div>
                        @include('admin.rooms.partials.image_upload_field')
                    </div>
                </div>
            </div>

        </div>

        <hr class="mt-4">
        <x-form.actions back-url="{{ route('admin.rooms') }}" submit-text="Simpan Ruangan" />
    </x-form.card>
@stop

@section('css')
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
@endpush

@include('admin.partials.timezone_detector')
