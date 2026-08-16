@extends('adminlte::page')

@section('title', 'Tambah Reservasi')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Reservasi</h1>
        <div class="page-subtitle">Buat jadwal penggunaan ruangan untuk pengguna internal.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="row">

        {{-- Main Form --}}
        <div class="col-lg-12">
            <x-form.card action="{{ route('admin.reservations.store') }}" submit-guard loading-text="Menyimpan...">
                <x-form.section title="Pemohon & Kebutuhan Ruangan" />

                <div class="row">
                    <x-form.field name="user_id" label="Pegawai/Pemohon" col-class="col-lg-6 col-md-6">
                        <select id="user_id" name="user_id" class="form-control">
                            <option value="">-- Gunakan akun login (Admin) --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') === $user->id)>
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
                                data-facilities="{{ $room->facilities->pluck('slug')->implode(',') }}"
                                @selected(old('room_id') === $room->id)>
                                {{ $room->name }} (Lantai {{ $room->floor }}) - Kapasitas: {{ $room->capacity }}
                            </option>
                        @endforeach
                    </select>
                </x-form.field>

                <div class="row">
                    <x-form.field name="reservation_date" label="Tanggal" type="date"
                        value="{{ old('reservation_date') }}" required col-class="col-lg-4 col-md-6" />

                    <x-form.field name="start_clock" label="Jam Mulai" required col-class="col-lg-4 col-md-6">
                        <input type="text" id="start_clock" name="start_clock" class="form-control js-timepicker"
                            value="{{ old('start_clock') }}" placeholder="Pilih jam" autocomplete="off" required>
                    </x-form.field>

                    <x-form.field name="end_clock" label="Jam Selesai" required col-class="col-lg-4 col-md-6">
                        <input type="text" id="end_clock" name="end_clock" class="form-control js-timepicker"
                            value="{{ old('end_clock') }}" placeholder="Pilih jam" autocomplete="off" required>
                    </x-form.field>
                </div>

                <div class="row">
                    <x-form.field name="visitor_count" label="Jumlah Pengunjung" type="number"
                        value="{{ old('visitor_count', 1) }}" min="1" required col-class="col-lg-6 col-md-6" />
                </div>

                <div class="form-group">
                    <label class="font-weight-semibold">Permintaan Konsumsi</label>
                    <div class="custom-control custom-checkbox mb-1">
                        <input type="checkbox" class="custom-control-input" id="with_snack" name="with_snack" value="1"
                            {{ old('with_snack') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="with_snack">Snack / Kudapan</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="with_lunch" name="with_lunch" value="1"
                            {{ old('with_lunch') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="with_lunch">Makan Siang</label>
                    </div>
                </div>

                <x-form.field name="purpose" label="Tujuan" type="textarea" value="{{ old('purpose') }}" rows="3"
                    hint="Opsional: Jelaskan keperluan reservasi ruangan ini." col-class="col-md-12" />

                <div class="alert alert-info py-2 small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Reservasi yang dibuat dari web admin langsung berstatus
                    <span class="badge badge-success badge-sm">DISETUJUI</span>.
                </div>

                <x-form.actions back-url="{{ route('admin.reservations') }}" submit-text="Simpan" />
            </x-form.card>

            @if (config('app.debug'))
                <div class="card card-outline card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bug mr-1"></i> Mode Debug: cURL Uji CSP</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-2 text-muted">
                            Isi form reservasi dulu, lalu klik tombol berikut untuk membuat cURL uji CSP berdasarkan data
                            yang kamu input.
                        </p>
                        <button type="button" id="generate-csp-curl" class="btn btn-warning btn-sm mb-3">
                            <i class="fas fa-terminal mr-1"></i> Generate cURL dari Input Form
                        </button>
                        <div id="csp-curl-wrapper" class="d-none">
                            <textarea id="csp-curl-output" class="form-control" rows="16" readonly></textarea>
                        </div>
                    </div>
                </div>
            @endif
        </div>


    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @include('admin.partials.form_submit_guard_script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDebugMode = @json(config('app.debug'));
            const baseUrl = @json(url('/'));

            const toMinutes = (value) => {
                if (!value || !value.includes(':')) {
                    return null;
                }

                const [hour, minute] = value.split(':').map(Number);
                return (hour * 60) + minute;
            };

            @include('admin.reservations.partials.required_facility_filter_script')

            initializeRequiredFacilityFilter();

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

            if (isDebugMode) {
                const generateButton = document.getElementById('generate-csp-curl');
                const outputWrapper = document.getElementById('csp-curl-wrapper');
                const outputArea = document.getElementById('csp-curl-output');

                generateButton?.addEventListener('click', function() {
                    const roomId = document.getElementById('room_id')?.value || 'ROOM_ID';
                    const reservationDate = document.getElementById('reservation_date')?.value ||
                        '2026-03-01';
                    const startClock = document.getElementById('start_clock')?.value || '09:00';
                    const endClock = document.getElementById('end_clock')?.value || '10:00';
                    const visitorCount = document.getElementById('visitor_count')?.value || '1';
                    const purpose = document.getElementById('purpose')?.value || 'Uji CSP dari form web';

                    const startDateTime = `${reservationDate} ${startClock}:00`;
                    const endDateTime = `${reservationDate} ${endClock}:00`;

                    const payload = {
                        room_id: roomId,
                        start_time: startDateTime,
                        end_time: endDateTime,
                        purpose,
                        visitor_count: Number(visitorCount),
                    };

                    const jsonPayload = JSON.stringify(payload, null, 4);

                    const curlText = `URL:\n${baseUrl}/api/v1/reservations\n\n` +
                        `BODY:\n${jsonPayload}\n\n` +
                        `cURL:\n` +
                        `curl -X POST '${baseUrl}/api/v1/reservations' \\\n` +
                        `  -H 'Content-Type: application/json' \\\n` +
                        `  -d '${jsonPayload}'`;

                    outputArea.value = curlText;
                    outputWrapper.classList.remove('d-none');
                });
            }
        });
    </script>
@endpush

@include('admin.partials.timezone_detector')
