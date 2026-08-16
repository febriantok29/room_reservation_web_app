@extends('adminlte::page')

@section('title', 'Kalender Reservasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Kalender Reservasi</h1>
            <div class="page-subtitle">Kelola jadwal pemakaian ruangan dalam tampilan kalender interaktif.</div>
        </div>
        <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Reservasi
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    {{-- Filter Bar --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                    <label class="mb-1 text-sm font-weight-bold">Filter Ruangan</label>
                    <select id="roomFilter" class="form-control form-control-sm">
                        <option value="">Semua Ruangan</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }} (Lantai {{ $room->floor }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5 col-md-6 mb-2 mb-lg-0">
                    <label class="mb-1 text-sm font-weight-bold">Filter Status</label>
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusPending" value="pending" checked>
                            <span class="badge badge-warning ml-1">Menunggu</span>
                        </label>
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusApproved" value="approved" checked>
                            <span class="badge badge-success ml-1">Disetujui</span>
                        </label>
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusRejected" value="rejected">
                            <span class="badge badge-danger ml-1">Ditolak</span>
                        </label>
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusCompleted" value="completed">
                            <span class="badge badge-primary ml-1">Selesai</span>
                        </label>
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusCancelled" value="cancelled">
                            <span class="badge badge-secondary ml-1">Dibatalkan</span>
                        </label>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12 text-right">
                    <label class="mb-1 text-sm font-weight-bold d-block">Legenda</label>
                    <div class="text-sm" style="line-height:1.8;">
                        <i class="fas fa-square text-warning"></i> Menunggu &nbsp;
                        <i class="fas fa-square text-success"></i> Disetujui<br>
                        <i class="fas fa-square text-danger"></i> Ditolak &nbsp;
                        <i class="fas fa-square text-primary"></i> Selesai &nbsp;
                        <i class="fas fa-square text-secondary"></i> Dibatalkan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar Container --}}
    <div class="card card-admin">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    {{-- Modal for Event Details --}}
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Reservasi</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
                <div class="modal-footer flex-wrap" style="gap:.35rem;" id="eventModalFooter">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Create Reservation --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="createReservationForm">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle mr-2"></i>Buat Reservasi Baru
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="createFormErrors" class="alert alert-danger d-none"></div>

                        {{-- Jadwal --}}
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-calendar-alt mr-2"></i>Jadwal</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="create_date" name="reservation_date" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" id="create_start_time" name="start_clock" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" id="create_end_time" name="end_clock" class="form-control"
                                    required>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Ruangan & Detail --}}
                        <h6 class="font-weight-bold mb-3"><i class="fas fa-door-open mr-2"></i>Detail Reservasi</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-semibold">Ruangan <span class="text-danger">*</span></label>
                                <select id="create_room" name="room_id" class="form-control" required>
                                    <option value="">-- Pilih Ruangan --</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            {{ $room->name }} (Lantai {{ $room->floor }}) - Kapasitas:
                                            {{ $room->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-semibold">Visitor<span class="text-danger">*</span></label>
                                <input type="number" id="create_visitor_count" name="visitor_count"
                                    class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-semibold">Pemohon (Opsional)</label>
                                <select id="create_user" name="user_id" class="form-control">
                                    <option value="">-- Admin --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->first_name }} {{ $user->last_name }} -
                                            {{ $user->employee_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="create_with_snack"
                                        name="with_snack" value="1">
                                    <label class="custom-control-label" for="create_with_snack">Dengan Snack</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="create_with_lunch"
                                        name="with_lunch" value="1">
                                    <label class="custom-control-label" for="create_with_lunch">Dengan Makan Siang</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-semibold">Keperluan (Opsional)</label>
                            <textarea id="create_purpose" name="purpose" class="form-control" rows="3"
                                placeholder="Jelaskan tujuan reservasi ruangan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                            <i class="fas fa-save mr-1"></i>Simpan Reservasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* FullCalendar custom styles */
        #calendar {
            font-size: 0.9rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .fc .fc-button {
            padding: 0.3rem 0.65rem;
            font-size: 0.875rem;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 3px;
        }

        .fc-event:hover {
            opacity: 0.85;
        }

        .fc-timegrid-event {
            border-left-width: 3px;
        }

        /* Status badges in modal */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-approved {
            background: #28a745;
            color: #fff;
        }

        .status-rejected {
            background: #dc3545;
            color: #fff;
        }

        .status-completed {
            background: #007bff;
            color: #fff;
        }

        .status-cancelled {
            background: #6c757d;
            color: #fff;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/id.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(function() {
            const calendarEl = document.getElementById('calendar');
            const eventModal = $('#eventModal');
            const eventModalBody = $('#eventModalBody');
            const eventModalFooter = $('#eventModalFooter');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Initialize calendar
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek'
                },
                buttonText: {
                    today: 'Hari Ini',
                    day: 'Hari',
                    week: 'Minggu',
                    month: 'Bulan',
                    list: 'Daftar'
                },
                locale: 'id',
                slotMinTime: '07:00:00',
                slotMaxTime: '20:00:00',
                allDaySlot: false,
                height: 'auto',
                navLinks: true,
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                nowIndicator: true,

                // Load events from server
                events: function(info, successCallback, failureCallback) {
                    const roomId = $('#roomFilter').val();
                    const statusFilters = getSelectedStatuses();

                    $.ajax({
                        url: '{{ route('admin.reservations.calendar.events') }}',
                        type: 'GET',
                        data: {
                            start: info.startStr,
                            end: info.endStr,
                            room_id: roomId,
                            status: statusFilters
                        },
                        success: function(data) {
                            successCallback(data);
                        },
                        error: function() {
                            failureCallback();
                            alert('Gagal memuat data reservasi');
                        }
                    });
                },

                // Event click - show details
                eventClick: function(info) {
                    const event = info.event;
                    const props = event.extendedProps;

                    const statusClass = 'status-' + props.status;
                    const statusLabel = getStatusLabel(props.status);

const rid = esc(props.reservation_id);
                    const rName = esc(props.room_name || '-');
                    const uName = esc(props.user_name || '-');
                    const uDivision = esc(props.user_division || '-');
                    const purpose = props.purpose ? esc(props.purpose) : '';

                    const html = `
                        <div class="mb-3">
                            <span class="status-badge ${statusClass}">${statusLabel}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-hashtag mr-1"></i>ID Reservasi:</strong><br>
                            <span class="text-monospace">${rid}</span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-door-open mr-1"></i>Ruangan:</strong><br>
                            ${rName}
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-user mr-1"></i>Pemohon:</strong><br>
                            ${uName}${uDivision && uDivision !== '-' ? ` <span class="text-muted">(${uDivision})</span>` : ''}
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-clock mr-1"></i>Waktu:</strong><br>
                            ${formatDateTime(event.start)} &ndash; ${formatTime(event.end)}
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-users mr-1"></i>Jumlah Pengunjung:</strong><br>
                            ${props.visitor_count} orang
                        </div>
                        ${(props.with_snack || props.with_lunch) ? `<div class="mb-2"><strong><i class="fas fa-utensils mr-1"></i>Konsumsi:</strong><br>${props.with_snack ? '<span class="badge badge-warning">Snack</span> ' : ''}${props.with_lunch ? '<span class="badge badge-info">Makan Siang</span>' : ''}</div>` : ''}
                        ${purpose ? `<div class="mb-2"><strong><i class="fas fa-clipboard-list mr-1"></i>Keperluan:</strong><br>${purpose}</div>` : ''}
                    `;

                    eventModalBody.html(html);
                    updateModalFooter(props.status, props.reservation_id);
                    eventModal.modal('show');
                },

                // Event drag & drop - update time
                eventDrop: function(info) {
                    Swal.fire({
                        title: 'Ubah Jadwal?',
                        text: 'Jadwal reservasi ini akan diperbarui sesuai posisi baru.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, ubah',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#007bff',
                    }).then(function(result) {
                        if (!result.isConfirmed) { info.revert(); return; }

                        const event = info.event;
                        const props = event.extendedProps;

                        $.ajax({
                            url: '/admin/reservations/' + props.reservation_id + '/time',
                            type: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            data: {
                                start_time: event.start.toISOString(),
                                end_time: event.end.toISOString()
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Jadwal berhasil diperbarui.', timer: 2000, showConfirmButton: false });
                                } else {
                                    info.revert();
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: response.message || 'Gagal memperbarui jadwal.' });
                                }
                            },
                            error: function(xhr) {
                                info.revert();
                                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Gagal memperbarui jadwal.' });
                            }
                        });
                    });
                },

                // Event resize - update end time
                eventResize: function(info) {
                    Swal.fire({
                        title: 'Ubah Durasi?',
                        text: 'Durasi reservasi ini akan diperbarui sesuai ukuran baru.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, ubah',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#007bff',
                    }).then(function(result) {
                        if (!result.isConfirmed) { info.revert(); return; }

                        const event = info.event;
                        const props = event.extendedProps;

                        $.ajax({
                            url: '/admin/reservations/' + props.reservation_id + '/time',
                            type: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            data: {
                                start_time: event.start.toISOString(),
                                end_time: event.end.toISOString()
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Durasi berhasil diperbarui.', timer: 2000, showConfirmButton: false });
                                } else {
                                    info.revert();
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: response.message || 'Gagal memperbarui durasi.' });
                                }
                            },
                            error: function(xhr) {
                                info.revert();
                                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Gagal memperbarui durasi.' });
                            }
                        });
                    });
                },

                // Date select - create new reservation
                select: function(info) {
                    // Open create modal
                    openCreateModal(info.start, info.end);
                    // Unselect after opening modal
                    calendar.unselect();
                }
            });

            calendar.render();

            // Filter handlers
            $('#roomFilter').on('change', function() {
                calendar.refetchEvents();
            });

            $('input[type="checkbox"][id^="status"]').on('change', function() {
                calendar.refetchEvents();
            });

            // Open Create Modal with prefilled datetime
            function openCreateModal(startDate, endDate) {
                const createModal = $('#createModal');
                const createFormErrors = $('#createFormErrors');

                // Hide previous errors
                createFormErrors.addClass('d-none').html('');

                // Reset form
                $('#createReservationForm')[0].reset();

                // Format date to YYYY-MM-DD
                const dateStr = startDate.toISOString().split('T')[0];
                $('#create_date').val(dateStr);

                // Check if this is from month view (allDay) or time view
                const isAllDay = endDate - startDate >= 86400000; // 24 hours or more

                if (isAllDay) {
                    // Month view: just set date, let user pick time
                    $('#create_start_time').val('08:00');
                    $('#create_end_time').val('10:00');
                } else {
                    // Time view: set specific times
                    const startTimeStr = formatTimeForInput(startDate);
                    const endTimeStr = formatTimeForInput(endDate);
                    $('#create_start_time').val(startTimeStr);
                    $('#create_end_time').val(endTimeStr);
                }

                // Show modal
                createModal.modal('show');
            }

            // Handle Create Form Submit
            $('#createReservationForm').on('submit', function(e) {
                e.preventDefault();

                const submitBtn = $('#createSubmitBtn');
                const originalText = submitBtn.html();
                const createFormErrors = $('#createFormErrors');

                // Disable button and show loading
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
                createFormErrors.addClass('d-none').html('');

                const formData = $(this).serialize();

                $.ajax({
                    url: '{{ route('admin.reservations.store') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: formData,
                    success: function(response) {
                        $('#createModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Reservasi berhasil dibuat.', timer: 2000, showConfirmButton: false });
                        calendar.refetchEvents();
                        $('#createReservationForm')[0].reset();
                    },
                    error: function(xhr) {

                        // Show error messages
                        const responseData = xhr.responseJSON || {};
                        const errors = responseData.errors || {};
                        let errorHtml =
                            '<strong>Terjadi kesalahan:</strong><ul class="mb-0 pl-3">';

                        // Check if errors is an object with properties
                        if (typeof errors === 'object' && errors !== null && !Array.isArray(
                                errors) && Object.keys(errors).length > 0) {
                            $.each(errors, function(field, messages) {
                                if (Array.isArray(messages)) {
                                    $.each(messages, function(index, message) {
                                        errorHtml += '<li>' + message + '</li>';
                                    });
                                } else {
                                    errorHtml += '<li>' + messages + '</li>';
                                }
                            });
                        } else {
                            // Fallback to main message
                            errorHtml += '<li>' + (responseData.message ||
                                'Gagal membuat reservasi') + '</li>';
                        }

                        errorHtml += '</ul>';
                        createFormErrors.removeClass('d-none').html(errorHtml);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // ── Helper functions ────────────────────────────────────────────

            function getSelectedStatuses() {
                const statuses = [];
                $('input[type="checkbox"][id^="status"]:checked').each(function() {
                    statuses.push($(this).val());
                });
                return statuses.join(',');
            }

            function getStatusLabel(status) {
                const labels = {
                    pending: 'Menunggu',
                    approved: 'Disetujui',
                    rejected: 'Ditolak',
                    completed: 'Selesai',
                    cancelled: 'Dibatalkan'
                };
                return labels[status] || status.toUpperCase();
            }

            // Update modal footer buttons based on current reservation status
            function updateModalFooter(status, reservationId) {
                const editUrl = '/admin/reservations/' + reservationId + '/edit';
                let html = `
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <a href="${editUrl}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye mr-1"></i>Lihat Detail
                    </a>
                `;

                if (status === 'pending') {
                    html += `
                        <button type="button" class="btn btn-danger btn-sm"
                            onclick="doCalendarAction('reject','${reservationId}','Tolak Reservasi','Reservasi ini akan ditolak. Pemohon akan diberitahu.','Tolak','#dc3545')">
                            <i class="fas fa-times mr-1"></i>Tolak
                        </button>
                        <button type="button" class="btn btn-success btn-sm"
                            onclick="doCalendarAction('approve','${reservationId}','Setujui Reservasi','Reservasi ini akan disetujui. Pemohon akan diberitahu.','Setujui','#28a745')">
                            <i class="fas fa-check mr-1"></i>Setujui
                        </button>
                    `;
                } else if (status === 'approved') {
                    html += `
                        <button type="button" class="btn btn-primary btn-sm"
                            onclick="doCalendarAction('complete','${reservationId}','Selesaikan Reservasi','Tandai reservasi ini sebagai selesai.','Selesaikan','#007bff')">
                            <i class="fas fa-check-double mr-1"></i>Selesaikan
                        </button>
                    `;
                }

                eventModalFooter.html(html);
            }

            // Generic action handler for approve / reject / complete / cancel
            window.doCalendarAction = function(action, reservationId, title, text, confirmText, confirmColor) {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: confirmColor,
                    reverseButtons: true,
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    const urlMap = {
                        approve:  '/admin/approvals/'    + reservationId + '/approve',
                        reject:   '/admin/approvals/'    + reservationId + '/reject',
                        complete: '/admin/reservations/' + reservationId + '/complete',
                        cancel:   '/admin/reservations/' + reservationId + '/cancel',
                    };

                    $.ajax({
                        url: urlMap[action],
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        success: function(response) {
                            eventModal.modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Aksi berhasil dijalankan.',
                                timer: 2500,
                                showConfirmButton: false,
                            });
                            calendar.refetchEvents();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan, coba lagi.',
                            });
                        }
                    });
                });
            };

            function formatDateTime(date) {
                return new Date(date).toLocaleString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function formatTime(date) {
                return new Date(date).toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function esc(val) {
                const el = document.createElement('span');
                el.textContent = val;
                return el.innerHTML;
            }

            function formatTimeForInput(date) {
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return hours + ':' + minutes;
            }
        });
    </script>
@endpush

@include('admin.partials.timezone_detector')
