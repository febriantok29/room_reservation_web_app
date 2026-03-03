@extends('adminlte::page')

@section('title', 'Ubah Ruangan')

@section('content_header')
    <div>
        <h1 class="m-0">Ubah Ruangan</h1>
        <div class="page-subtitle">Perbarui detail ruangan, fasilitas, dan status maintenance.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.rooms.update', $room->id) }}" method="PUT" loading-text="Memperbarui...">
        <x-form.section title="Informasi Dasar">
            <x-form.row>
                <x-form.field name="name" label="Nama Ruangan" type="text" :value="old('name', $room->name)" col-class="col-lg-4 col-md-6"
                    required />

                <x-form.field name="location" label="Lokasi" type="text" :value="old('location', $room->location)" col-class="col-lg-4 col-md-6"
                    hint="Contoh: Lantai 1, Gedung A" required />

                <x-form.field name="capacity" label="Kapasitas (Orang)" type="number" :value="old('capacity', $room->capacity)"
                    col-class="col-lg-4 col-md-6" min="1" required />
            </x-form.row>

            <x-form.field name="description" label="Deskripsi" type="textarea" :value="old('description', $room->description)" col-class=""
                rows="3" hint="Opsional: Jelaskan kegunaan atau fitur khusus ruangan ini." />
        </x-form.section>

        <x-form.section title="Fasilitas & Status">
            <x-form.row>
                <div class="col-lg-6">
                    @include('admin.rooms.partials.facility_chip_input_field', [
                        'hiddenValue' => old(
                            'facility_ids_input',
                            $room->facilities->pluck('name')->implode(', ')),
                    ])
                </div>
            </x-form.row>

            <div class="form-group form-check">
                <input type="checkbox" id="is_maintenance" name="is_maintenance" value="1" class="form-check-input"
                    @checked(old('is_maintenance', $room->is_maintenance))>
                <label class="form-check-label" for="is_maintenance">Sedang maintenance</label>
            </div>
        </x-form.section>

        <x-form.actions back-url="{{ route('admin.rooms') }}" submit-text="Perbarui" />
    </x-form.card>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('admin.partials.form_submit_guard_script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allFacilities = @json($allFacilities);
            const selectedFacilities = @json($room->facilities);

            @include('admin.rooms.partials.facility_chip_input_script')

            initializeRoomFacilitySelect({
                allFacilities: allFacilities,
                selectedFacilities: selectedFacilities
            });
        });
    </script>
@stop
