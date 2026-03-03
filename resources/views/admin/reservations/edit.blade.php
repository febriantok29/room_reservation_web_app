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

    <div class="card card-admin">
        <div class="card-body">
            <form action="{{ route('admin.reservations.update', $reservation->id) }}" method="POST" data-submit-guard
                data-loading-text="Memperbarui...">
                @csrf
                @method('PUT')

                <div class="form-section-title">Pemohon & Kebutuhan Ruangan</div>

                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <label for="user_id">Pegawai/Pemohon</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <option value="">-- Pertahankan pengguna saat ini --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $reservation->user_id) === $user->id)>
                                        {{ $user->full_name }} - {{ $user->employee_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        @include('admin.reservations.partials.required_facility_filter_field')
                    </div>
                </div>

                <div class="form-section-title">Jadwal & Detail Reservasi</div>

                <div class="form-group">
                    <label for="room_id">Ruangan <span class="text-danger">*</span></label>
                    <select id="room_id" name="room_id" class="form-control" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}"
                                data-facilities="{{ $room->facilities->pluck('slug')->implode(',') }}"
                                @selected(old('room_id', $reservation->room_id) === $room->id)>
                                {{ $room->name }} ({{ $room->location }}) - Kapasitas: {{ $room->capacity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="reservation_date">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" id="reservation_date" name="reservation_date" class="form-control"
                                value="{{ old('reservation_date', optional($reservation->start_time)->format('Y-m-d')) }}"
                                required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="start_clock">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="text" id="start_clock" name="start_clock" class="form-control js-timepicker"
                                value="{{ old('start_clock', optional($reservation->start_time)->format('H:i')) }}"
                                placeholder="Pilih jam" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="end_clock">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="text" id="end_clock" name="end_clock" class="form-control js-timepicker"
                                value="{{ old('end_clock', optional($reservation->end_time)->format('H:i')) }}"
                                placeholder="Pilih jam" autocomplete="off" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <label for="visitor_count">Jumlah Pengunjung <span class="text-danger">*</span></label>
                            <input type="number" id="visitor_count" name="visitor_count" class="form-control"
                                value="{{ old('visitor_count', $reservation->visitor_count) }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">Tujuan</label>
                    <textarea id="purpose" name="purpose" class="form-control" rows="3">{{ old('purpose', $reservation->purpose) }}</textarea>
                    <small class="text-muted">Opsional: Jelaskan keperluan reservasi ruangan ini.</small>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.reservations') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
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
