@extends('adminlte::page')

@section('title', in_array($reservation->status, ['completed', 'rejected', 'cancelled']) ? 'Detail Reservasi' : 'Ubah
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

    @php
        $sColor = match ($reservation->status) {
            'pending' => ['badge' => 'warning', 'border' => '#ffc107', 'icon' => 'fas fa-clock'],
            'approved' => ['badge' => 'success', 'border' => '#28a745', 'icon' => 'fas fa-check-circle'],
            'rejected' => ['badge' => 'danger', 'border' => '#dc3545', 'icon' => 'fas fa-times-circle'],
            'completed' => ['badge' => 'primary', 'border' => '#007bff', 'icon' => 'fas fa-flag-checkered'],
            default => ['badge' => 'secondary', 'border' => '#6c757d', 'icon' => 'fas fa-ban'],
        };
        $sLabel = match ($reservation->status) {
            'pending' => 'MENUNGGU',
            'approved' => 'DISETUJUI',
            'rejected' => 'DITOLAK',
            'completed' => 'SELESAI',
            'cancelled' => 'DIBATALKAN',
            default => strtoupper($reservation->status),
        };
    @endphp

    @if (in_array($reservation->status, ['completed', 'rejected', 'cancelled']))

        {{-- ============================================================
             READ-ONLY: Receipt / Ticket style card
        ============================================================ --}}
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card card-admin" style="border-left:4px solid {{ $sColor['border'] }};">

                    {{-- Receipt Header --}}
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="text-muted small font-weight-bold mb-1">ID RESERVASI</div>
                            <div class="font-weight-bold" style="font-size:1.1rem;">{{ $reservation->id }}</div>
                        </div>
                        <span class="badge badge-{{ $sColor['badge'] }}" style="font-size:.85rem;padding:.45em .75em;">
                            <i class="{{ $sColor['icon'] }} mr-1"></i>{{ $sLabel }}
                        </span>
                    </div>

                    {{-- Dashed divider (ticket perforation feel) --}}
                    <div style="border-top:2px dashed #dee2e6;margin:0 1rem;"></div>

                    {{-- Receipt Body --}}
                    <div class="card-body py-3">

                        <div class="row mb-2">
                            <div class="col-5 text-muted small font-weight-bold">Pemohon</div>
                            <div class="col-7 small">{{ $reservation->user?->full_name ?? '-' }}
                                @if ($reservation->user?->employee_id)
                                    <span class="text-muted">({{ $reservation->user->employee_id }})</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5 text-muted small font-weight-bold">Ruangan</div>
                            <div class="col-7 small">
                                {{ $reservation->room?->name ?? '-' }}
                                @if ($reservation->room?->floor)
                                    <span class="text-muted">· Lantai {{ $reservation->room->floor }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5 text-muted small font-weight-bold">Tanggal</div>
                            <div class="col-7 small">{{ $reservation->start_time_label }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5 text-muted small font-weight-bold">Waktu</div>
                            <div class="col-7 small">
                                {{ $reservation->start_time_local->format('H:i') }}
                                &ndash;
                                {{ $reservation->end_time_local->format('H:i') }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5 text-muted small font-weight-bold">Jumlah Pengunjung</div>
                            <div class="col-7 small">{{ $reservation->visitor_count }} orang</div>
                        </div>

                        @if ($reservation->required_facilities)
                            <div class="row mb-2">
                                <div class="col-5 text-muted small font-weight-bold">Fasilitas Diminta</div>
                                <div class="col-7 small">{{ $reservation->required_facilities }}</div>
                            </div>
                        @endif

                        <div class="row mb-2">
                            <div class="col-5 text-muted small font-weight-bold">Tujuan</div>
                            <div class="col-7 small">{{ $reservation->purpose ?? '-' }}</div>
                        </div>

                    </div>

                    {{-- Dashed divider --}}
                    <div style="border-top:2px dashed #dee2e6;margin:0 1rem;"></div>

                    <div class="card-footer">
                        <a href="{{ route('admin.reservations') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- ============================================================
             EDIT MODE: 8+4 layout with sidebar summary
        ============================================================ --}}
        <div class="row">

            {{-- Main Form (8 columns) --}}
            <div class="col-lg-8">
                <x-form.card action="{{ route('admin.reservations.update', $reservation->id) }}" method="PUT"
                    submit-guard loading-text="Memperbarui...">
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

                    <x-form.field name="purpose" label="Tujuan" type="textarea"
                        value="{{ old('purpose', $reservation->purpose) }}" rows="3"
                        hint="Opsional: Jelaskan keperluan reservasi ruangan ini." col-class="col-md-12" />

                    <x-form.actions back-url="{{ route('admin.reservations') }}" submit-text="Perbarui" />
                </x-form.card>
            </div>

            {{-- Sidebar: current reservation summary (4 columns) --}}
            <div class="col-lg-4">
                <div class="card card-admin sticky-top" style="top:70px;border-left:4px solid {{ $sColor['border'] }};">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><i class="fas fa-receipt mr-2"></i> Reservasi Saat Ini</h3>
                        <span class="badge badge-{{ $sColor['badge'] }}">
                            <i class="{{ $sColor['icon'] }} mr-1"></i>{{ $sLabel }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-3 py-2">
                                <div class="text-muted" style="font-size:.75rem;">ID</div>
                                <div class="small font-weight-bold">{{ $reservation->id }}</div>
                            </li>
                            <li class="list-group-item px-3 py-2">
                                <div class="text-muted" style="font-size:.75rem;">Pemohon</div>
                                <div class="small">{{ $reservation->user?->full_name ?? '-' }}</div>
                            </li>
                            <li class="list-group-item px-3 py-2">
                                <div class="text-muted" style="font-size:.75rem;">Ruangan</div>
                                <div class="small">{{ $reservation->room?->name ?? '-' }}</div>
                            </li>
                            <li class="list-group-item px-3 py-2">
                                <div class="text-muted" style="font-size:.75rem;">Jadwal</div>
                                <div class="small">{{ $reservation->start_time_label }}</div>
                                <div class="small text-muted">
                                    {{ $reservation->start_time_local->format('H:i') }}
                                    &ndash;
                                    {{ $reservation->end_time_local->format('H:i') }}
                                </div>
                            </li>
                            <li class="list-group-item px-3 py-2">
                                <div class="text-muted" style="font-size:.75rem;">Pengunjung</div>
                                <div class="small">{{ $reservation->visitor_count }} orang</div>
                            </li>
                            @if ($reservation->purpose)
                                <li class="list-group-item px-3 py-2">
                                    <div class="text-muted" style="font-size:.75rem;">Tujuan</div>
                                    <div class="small">{{ Str::limit($reservation->purpose, 80) }}</div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

        </div>

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
