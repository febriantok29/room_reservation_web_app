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

    <div class="card card-admin">
        <div class="card-body">
            <form action="{{ route('admin.rooms.update', $room->id) }}" method="POST" data-submit-guard
                data-loading-text="Memperbarui...">
                @csrf
                @method('PUT')

                <div class="form-section-title">Informasi Dasar</div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="name">Nama Ruangan</label>
                            <input type="text" id="name" name="name" class="form-control"
                                value="{{ old('name', $room->name) }}" required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="location">Lokasi</label>
                            <input type="text" id="location" name="location" class="form-control"
                                value="{{ old('location', $room->location) }}" required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="capacity">Kapasitas</label>
                            <input type="number" id="capacity" name="capacity" class="form-control"
                                value="{{ old('capacity', $room->capacity) }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $room->description) }}</textarea>
                </div>

                <div class="form-section-title">Fasilitas & Status</div>

                @include('admin.rooms.partials.facility_chip_input_field', [
                    'hiddenValue' => old('facility_ids_input', $room->facilities->pluck('name')->implode(', ')),
                ])

                <div class="form-group form-check">
                    <input type="checkbox" id="is_maintenance" name="is_maintenance" value="1" class="form-check-input"
                        @checked(old('is_maintenance', $room->is_maintenance))>
                    <label class="form-check-label" for="is_maintenance">Sedang maintenance</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.rooms') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allFacilities = @json($allFacilities->pluck('name')->values());

            @include('admin.rooms.partials.facility_chip_input_script')

            initializeRoomFacilityChipInput({
                allFacilities,
            });
        });
    </script>
@stop
