@extends('adminlte::page')

@section('title', 'Ubah Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Ubah Reservasi</h1>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.reservations.update', $reservation->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-lg-6">
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
                    <div class="col-lg-6">
                        @include('admin.reservations.partials.required_facility_filter_field', [
                            'hiddenValue' => old('required_facilities_input'),
                        ])
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="room_id">Ruangan</label>
                            <select id="room_id" name="room_id" class="form-control" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}"
                                        data-facilities="{{ $room->facilities->pluck('slug')->implode(',') }}"
                                        @selected(old('room_id', $reservation->room_id) === $room->id)>
                                        {{ $room->name }} ({{ $room->location }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="reservation_date">Tanggal Reservasi</label>
                            <input type="date" id="reservation_date" name="reservation_date" class="form-control"
                                value="{{ old('reservation_date', optional($reservation->start_time)->format('Y-m-d')) }}"
                                required>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="start_clock">Jam Mulai</label>
                            <input type="text" id="start_clock" name="start_clock" class="form-control js-timepicker"
                                value="{{ old('start_clock', optional($reservation->start_time)->format('H:i')) }}"
                                placeholder="Pilih jam" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="end_clock">Jam Selesai</label>
                            <input type="text" id="end_clock" name="end_clock" class="form-control js-timepicker"
                                value="{{ old('end_clock', optional($reservation->end_time)->format('H:i')) }}"
                                placeholder="Pilih jam" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="visitor_count">Jumlah Pengunjung</label>
                            <input type="number" id="visitor_count" name="visitor_count" class="form-control"
                                value="{{ old('visitor_count', $reservation->visitor_count) }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">Tujuan</label>
                    <textarea id="purpose" name="purpose" class="form-control" rows="3">{{ old('purpose', $reservation->purpose) }}</textarea>
                </div>

                <small class="text-muted d-block mb-3">Gunakan jam kerja (contoh: 08:00 - 18:00).</small>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.reservations') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                allFacilities,
                prefillFromSelectedRoom: true,
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
