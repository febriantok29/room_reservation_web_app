@extends('adminlte::page')

@section('title', 'Tambah Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Tambah Reservasi</h1>
        @include('admin.partials.logout_button')
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.reservations.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="user_id">Pegawai/Pemohon</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <option value="">-- Gunakan akun login (Admin) --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') === $user->id)>
                                        {{ $user->full_name }} - {{ $user->employee_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="room_id">Ruangan</label>
                            <select id="room_id" name="room_id" class="form-control" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" @selected(old('room_id') === $room->id)>
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
                                value="{{ old('reservation_date') }}" required>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="start_clock">Jam Mulai</label>
                            <input type="text" id="start_clock" name="start_clock" class="form-control js-timepicker"
                                value="{{ old('start_clock') }}" placeholder="Pilih jam" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="end_clock">Jam Selesai</label>
                            <input type="text" id="end_clock" name="end_clock" class="form-control js-timepicker"
                                value="{{ old('end_clock') }}" placeholder="Pilih jam" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="visitor_count">Jumlah Pengunjung</label>
                            <input type="number" id="visitor_count" name="visitor_count" class="form-control"
                                value="{{ old('visitor_count', 1) }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="purpose">Tujuan</label>
                    <textarea id="purpose" name="purpose" class="form-control" rows="3">{{ old('purpose') }}</textarea>
                </div>

                <small class="text-muted d-block mb-3">Gunakan jam kerja (contoh: 08:00 - 18:00).</small>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.reservations') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @if (config('app.debug'))
        <div class="card card-outline card-warning mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bug mr-1"></i> Mode Debug: cURL Uji CSP</h3>
            </div>
            <div class="card-body">
                <p class="mb-2 text-muted">
                    Isi form reservasi dulu, lalu klik tombol berikut untuk membuat cURL uji CSP berdasarkan data yang kamu
                    input.
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
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDebugMode = @json(config('app.debug'));
            const baseUrl = @json(url('/'));
            const debugEmail = @json($debugUser?->email ?? 'staff1@roomreservation.com');
            const debugPassword = @json($debugUser?->role === 'admin' ? 'Admin@123' : ($debugUser?->role === 'staff' ? 'Staff@123' : 'User@123'));

            const toMinutes = (value) => {
                if (!value || !value.includes(':')) {
                    return null;
                }

                const [hour, minute] = value.split(':').map(Number);
                return (hour * 60) + minute;
            };

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

                    const curlText = `# 1) Login admin (pemesanan pertama)\n` +
                        `curl -X POST '${baseUrl}/api/v1/auth/login' \\\n` +
                        `  -H 'Content-Type: application/json' \\\n` +
                        `  -d '{"email":"admin@roomreservation.com","password":"Admin@123"}'\n\n` +
                        `# 2) Buat reservasi pertama (seharusnya sukses)\n` +
                        `curl -X POST '${baseUrl}/api/v1/reservations' \\\n` +
                        `  -H 'Authorization: Bearer @{{ ADMIN_ACCESS_TOKEN }}' \\\n` +
                        `  -H 'Content-Type: application/json' \\\n` +
                        `  -d '{\n` +
                        `    "room_id": "${roomId}",\n` +
                        `    "start_time": "${startDateTime}",\n` +
                        `    "end_time": "${endDateTime}",\n` +
                        `    "purpose": ${JSON.stringify(purpose)},\n` +
                        `    "visitor_count": ${visitorCount}\n` +
                        `  }'\n\n` +
                        `# 3) Login pengguna acak berbeda (pemesan kedua)\n` +
                        `curl -X POST '${baseUrl}/api/v1/auth/login' \\\n` +
                        `  -H 'Content-Type: application/json' \\\n` +
                        `  -d '{"email":"${debugEmail}","password":"${debugPassword}"}'\n\n` +
                        `# 4) Buat reservasi bentrok pada slot yang sama (seharusnya gagal/konflik)\n` +
                        `curl -X POST '${baseUrl}/api/v1/reservations' \\\n` +
                        `  -H 'Authorization: Bearer @{{ RANDOM_USER_ACCESS_TOKEN }}' \\\n` +
                        `  -H 'Content-Type: application/json' \\\n` +
                        `  -d '{\n` +
                        `    "room_id": "${roomId}",\n` +
                        `    "start_time": "${startDateTime}",\n` +
                        `    "end_time": "${endDateTime}",\n` +
                        `    "purpose": "Uji CSP - booking bentrok",\n` +
                        `    "visitor_count": ${visitorCount}\n` +
                        `  }'`;

                    outputArea.value = curlText;
                    outputWrapper.classList.remove('d-none');
                });
            }
        });
    </script>
@stop
