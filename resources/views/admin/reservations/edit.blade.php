@extends('adminlte::page')

@section('title', 'Ubah Reservasi')

@section('content_header')
    <div>
        <h1 class="m-0">Ubah Reservasi</h1>
        <div class="page-subtitle">Sesuaikan jadwal, ruangan, atau detail permintaan reservasi.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.reservations.update', $reservation->id) }}" method="PUT" submit-guard
        loading-text="Memperbarui...">
        <x-form.section title="Pemohon & Kebutuhan Ruangan" />

        <div class="row">
            <x-form.field name="user_id" label="Pegawai/Pemohon" col-class="col-lg-6 col-md-6">
                <select id="user_id" name="user_id" class="form-control">
                    <option value="">-- Pertahankan pengguna saat ini --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $reservation->user_id) === $user->id)>
                            {{ $user->full_name }} - {{ $user->employee_id }}
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <div class="col-lg-6 col-md-6">
                @include('admin.reservations.partials.required_facility_filter_field')
            </div>
        </div>

        <x-form.section title="Jadwal & Detail Reservasi" />

        <x-form.field name="room_id" label="Ruangan" required col-class="col-md-12">
            <select id="room_id" name="room_id" class="form-control" required>
                <option value="">-- Pilih Ruangan --</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}"
                        data-facilities="{{ $room->facilities->pluck('slug')->implode(',') }}" @selected(old('room_id', $reservation->room_id) === $room->id)>
                        {{ $room->name }} (Lantai {{ $room->floor }}) - Kapasitas: {{ $room->capacity }}
                    </option>
                @endforeach
            </select>
        </x-form.field>

        <div class="row">
            <x-form.field name="reservation_date" label="Tanggal" type="date"
                value="{{ old('reservation_date', optional($reservation->start_time)->format('Y-m-d')) }}" required
                col-class="col-lg-4 col-md-6" />

            <x-form.field name="start_clock" label="Jam Mulai" required col-class="col-lg-4 col-md-6">
                <input type="text" id="start_clock" name="start_clock" class="form-control js-timepicker"
                    value="{{ old('start_clock', optional($reservation->start_time)->format('H:i')) }}"
                    placeholder="Pilih jam" autocomplete="off" required>
            </x-form.field>

            <x-form.field name="end_clock" label="Jam Selesai" required col-class="col-lg-4 col-md-6">
                <input type="text" id="end_clock" name="end_clock" class="form-control js-timepicker"
                    value="{{ old('end_clock', optional($reservation->end_time)->format('H:i')) }}" placeholder="Pilih jam"
                    autocomplete="off" required>
            </x-form.field>
        </div>

        <div class="row">
            <x-form.field name="visitor_count" label="Jumlah Pengunjung" type="number"
                value="{{ old('visitor_count', $reservation->visitor_count) }}" min="1" required
                col-class="col-lg-6 col-md-6" />
        </div>

        <x-form.field name="purpose" label="Tujuan" type="textarea" value="{{ old('purpose', $reservation->purpose) }}"
            rows="3" hint="Opsional: Jelaskan keperluan reservasi ruangan ini." col-class="col-md-12" />

        <x-form.actions back-url="{{ route('admin.reservations') }}" submit-text="Perbarui" />
    </x-form.card>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('admin.partials.form_submit_guard_script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toMinutes = (value) => {
                if (!value || !value.includes(':')) {
                    return null;
                }

                const [hour, minute] = value.split(':').map(Number);
                return (hour * 60) + minute;
            };

            const allFacilities = @json(
                $allFacilities->map(fn($facility) => [
                            'slug' => $facility->slug,
                            'name' => $facility->name,
                        ])->values());

            @include('admin.reservations.partials.required_facility_filter_script')

            initializeRequiredFacilityFilter({
                allFacilities
            });

            const pickerConfig = {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                minuteIncrement: 5,
                allowInput: false,
                clickOpens: true,
                disableMobile: true,
            };

            const startPicker = flatpickr('#start_clock', pickerConfig);
            const endPicker = flatpickr('#end_clock', {
                ...pickerConfig,
                onChange: function(selectedDates, dateStr) {
                    const startValue = startPicker.input.value;
                    const endValue = dateStr;

                    const startTotal = toMinutes(startValue);
                    const endTotal = toMinutes(endValue);

                    if (startTotal === null || endTotal === null) {
                        return;
                    }

                    if (endTotal < startTotal) {
                        startPicker.setDate(endValue, true, 'H:i');
                    }
                },
            });

            startPicker.set('onChange', function(selectedDates, dateStr) {
                const endValue = endPicker.input.value;
                const startValue = dateStr;

                const startTotal = toMinutes(startValue);
                const endTotal = toMinutes(endValue);

                if (startTotal === null || endTotal === null) {
                    return;
                }

                if (startTotal > endTotal) {
                    endPicker.setDate(startValue, true, 'H:i');
                }
            });

            const initialStart = toMinutes(startPicker.input.value);
            const initialEnd = toMinutes(endPicker.input.value);
            if (initialStart !== null && initialEnd !== null && initialEnd < initialStart) {
                startPicker.setDate(endPicker.input.value, true, 'H:i');
            }
        });
    </script>
@stop
