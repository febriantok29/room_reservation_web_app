@extends('adminlte::page')

@section('title',
    in_array($reservation->status, ['completed', 'rejected', 'cancelled'])
    ? 'Detail Reservasi'
    : 'Ubah
    Reservasi')

@section('content_header')
    <div>
        <h1 class="m-0">
            {{ in_array($reservation->status, ['completed', 'rejected', 'cancelled']) ? 'Detail Reservasi' : 'Ubah Reservasi' }}
        </h1>
        <div class="page-subtitle">
            @if (in_array($reservation->status, ['completed', 'rejected', 'cancelled']))
                Lihat detail reservasi dengan status final. Tidak dapat diubah lagi.
            @else
                Sesuaikan jadwal, ruangan, atau detail permintaan reservasi.
            @endif
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    @if (in_array($reservation->status, ['completed', 'rejected', 'cancelled']))
        {{-- Read-only view for final status --}}
        <div class="card card-admin">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Reservasi ini memiliki status final dan tidak dapat diubah.
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <label>Pegawai/Pemohon</label>
                            <div class="form-control-plaintext">{{ $reservation->user?->full_name ?? '-' }}
                                ({{ $reservation->user?->employee_id ?? '-' }})</div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <label>Kebutuhan Fasilitas</label>
                            <div class="form-control-plaintext">{{ $reservation->required_facilities ?? 'Tidak ada' }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ruangan</label>
                    <div class="form-control-plaintext">{{ $reservation->room?->name ?? '-' }}
                        ({{ $reservation->room?->location ?? '-' }}) - Kapasitas: {{ $reservation->room?->capacity ?? '-' }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <div class="form-control-plaintext">{{ $reservation->start_time_label }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label>Jam Mulai</label>
                            <div class="form-control-plaintext">{{ $reservation->start_time_local->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label>Jam Selesai</label>
                            <div class="form-control-plaintext">{{ $reservation->end_time_local->format('H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <label>Jumlah Pengunjung</label>
                            <div class="form-control-plaintext">{{ $reservation->visitor_count }}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tujuan</label>
                    <div class="form-control-plaintext">{{ $reservation->purpose ?? '-' }}</div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.reservations') }}" class="btn btn-secondary">Kembali</a>
                    <span
                        class="badge
                        @if ($reservation->status === 'completed') bg-success
                        @elseif($reservation->status === 'rejected') bg-danger
                        @elseif($reservation->status === 'cancelled') bg-secondary
                        @else bg-secondary @endif"
                        style="height: fit-content;">
                        {{ match ($reservation->status) {
                            'completed' => 'SELESAI',
                            'rejected' => 'DITOLAK',
                            'cancelled' => 'DIBATALKAN',
                            default => strtoupper($reservation->status),
                        } }}
                    </span>
                </div>
            </div>
        </div>
    @else
        {{-- Edit form for pending/approved status --}}
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
                            data-facilities="{{ $room->facilities->pluck('slug')->implode(',') }}"
                            @selected(old('room_id', $reservation->room_id) === $room->id)>
                            {{ $room->name }} (Lantai {{ $room->floor }}) - Kapasitas: {{ $room->capacity }}
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <div class="row">
                <x-form.field name="reservation_date" label="Tanggal" type="date"
                    value="{{ old('reservation_date', optional($reservation->start_time_local)->format('Y-m-d')) }}"
                    required col-class="col-lg-4 col-md-6" />

                <x-form.field name="start_clock" label="Jam Mulai" required col-class="col-lg-4 col-md-6">
                    <input type="text" id="start_clock" name="start_clock" class="form-control js-timepicker"
                        value="{{ old('start_clock', optional($reservation->start_time_local)->format('H:i')) }}"
                        placeholder="Pilih jam" autocomplete="off" required>
                </x-form.field>

                <x-form.field name="end_clock" label="Jam Selesai" required col-class="col-lg-4 col-md-6">
                    <input type="text" id="end_clock" name="end_clock" class="form-control js-timepicker"
                        value="{{ old('end_clock', optional($reservation->end_time_local)->format('H:i')) }}"
                        placeholder="Pilih jam" autocomplete="off" required>
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
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
        });
    </script>
@stop

@include('admin.partials.timezone_detector')
