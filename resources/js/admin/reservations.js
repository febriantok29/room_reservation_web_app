import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import idLocale from '@fullcalendar/core/locales/id';

const $ = window.jQuery;

const cfg = window.RapaCalendarConfig || {};

const calendarEl = document.getElementById('calendar');
const eventModal = $('#eventModal');
const eventModalBody = $('#eventModalBody');
const eventModalFooter = $('#eventModalFooter');
const csrfToken = cfg.csrfToken;

function fmt(date) {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    }).format(date);
}

// Build status summary list safely (no HTML interpolation of server messages).
function buildErrorList(responseData) {
    const ul = document.createElement('ul');
    ul.className = 'mb-0 pl-3';
    const errors = responseData.errors || {};
    const items = [];

    if (typeof errors === 'object' && errors !== null && !Array.isArray(errors) && Object.keys(errors).length > 0) {
        Object.values(errors).forEach((messages) => {
            const list = Array.isArray(messages) ? messages : [messages];
            list.forEach((m) => items.push(String(m)));
        });
    } else {
        items.push(String(responseData.message || 'Gagal membuat reservasi'));
    }

    items.forEach((text) => {
        const li = document.createElement('li');
        li.textContent = text;
        ul.append(li);
    });

    return ul;
}

// Local-time YYYY-MM-DD without shifting through UTC.
function localDateStr(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function getSelectedStatuses() {
    const statuses = [];
    document.querySelectorAll('input[type="checkbox"][id^="status"]:checked').forEach((el) => {
        statuses.push(el.value);
    });
    return statuses.join(',');
}

function getStatusLabel(status) {
    const labels = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak', completed: 'Selesai', cancelled: 'Dibatalkan' };
    return labels[status] || status.toUpperCase();
}

// Generic action handler for approve / reject / complete / cancel
window.doCalendarAction = function (action, reservationId, title, text, confirmText, confirmColor) {
    const suffix = { approve: '/approve', reject: '/reject', complete: '/complete' }[action];
    if (!suffix) return;
    const url = cfg.urls[action] + '/' + reservationId + suffix;

    Swal.fire({
        title, text, icon: 'question',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Kembali',
        confirmButtonColor: confirmColor,
        reverseButtons: true,
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url, type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            success(response) {
                eventModal.modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message || 'Aksi berhasil dijalankan.', timer: 2500, showConfirmButton: false });
                calendar.refetchEvents();
            },
            error(xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Terjadi kesalahan, coba lagi.' });
            },
        });
    });
};

function updateModalFooter(status, reservationId) {
    const editUrl = cfg.urls.edit + '/' + reservationId;
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

function openCreateModal(startDate, endDate) {
    const createFormErrors = $('#createFormErrors');
    createFormErrors.addClass('d-none').html('');
    $('#createReservationForm')[0].reset();

    $('#create_date').val(localDateStr(startDate));

    const isAllDay = endDate - startDate >= 86400000;
    if (isAllDay) {
        $('#create_start_time').val('08:00');
        $('#create_end_time').val('10:00');
    } else {
        $('#create_start_time').val(formatClock(startDate));
        $('#create_end_time').val(formatClock(endDate));
    }

    $('#createModal').modal('show');
}

function formatClock(date) {
    const h = String(date.getHours()).padStart(2, '0');
    const m = String(date.getMinutes()).padStart(2, '0');
    return h + ':' + m;
}

function makeCalendar() {
    return new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale: idLocale,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek',
        },
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

        events(info, successCallback, failureCallback) {
            $.ajax({
                url: cfg.urls.events,
                type: 'GET',
                data: {
                    start: info.startStr,
                    end: info.endStr,
                    room_id: $('#roomFilter').val(),
                    status: getSelectedStatuses(),
                },
                success: successCallback,
                error() {
                    failureCallback();
                    alert('Gagal memuat data reservasi');
                },
            });
        },

        eventClick(info) {
            const props = info.event.extendedProps;
            const statusClass = 'status-' + props.status;
            const statusLabel = getStatusLabel(props.status);

            const html = `
                <div class="mb-3"><span class="status-badge ${statusClass}">${statusLabel}</span></div>
                <div class="mb-2"><strong><i class="fas fa-hashtag mr-1"></i>ID Reservasi:</strong><br><span class="text-monospace">${esc(props.reservation_id)}</span></div>
                <div class="mb-2"><strong><i class="fas fa-door-open mr-1"></i>Ruangan:</strong><br>${esc(props.room_name || '-')}</div>
                <div class="mb-2"><strong><i class="fas fa-user mr-1"></i>Pemohon:</strong><br>${esc(props.user_name || '-')}${props.user_division && props.user_division !== '-' ? ` <span class="text-muted">(${esc(props.user_division)})</span>` : ''}</div>
                <div class="mb-2"><strong><i class="fas fa-clock mr-1"></i>Waktu:</strong><br>${formatDateTime(event.start)} &ndash; ${formatTime(event.end)}</div>
                <div class="mb-2"><strong><i class="fas fa-users mr-1"></i>Jumlah Pengunjung:</strong><br>${esc(props.visitor_count)} orang</div>
                ${(props.with_snack || props.with_lunch) ? `<div class="mb-2"><strong><i class="fas fa-utensils mr-1"></i>Konsumsi:</strong><br>${props.with_snack ? '<span class="badge badge-warning">Snack</span> ' : ''}${props.with_lunch ? '<span class="badge badge-info">Makan Siang</span>' : ''}</div>` : ''}
                ${props.purpose ? `<div class="mb-2"><strong><i class="fas fa-clipboard-list mr-1"></i>Keperluan:</strong><br>${esc(props.purpose)}</div>` : ''}
            `;

            eventModalBody.html(html);
            updateModalFooter(props.status, props.reservation_id);
            eventModal.modal('show');
        },

        eventDrop(info) {
            confirmReschedule(info, 'Ubah Jadwal?', 'Jadwal reservasi ini akan diperbarui sesuai posisi baru.', 'Jadwal berhasil diperbarui.');
        },

        eventResize(info) {
            confirmReschedule(info, 'Ubah Durasi?', 'Durasi reservasi ini akan diperbarui sesuai ukuran baru.', 'Durasi berhasil diperbarui.');
        },

        select(info) {
            openCreateModal(info.start, info.end);
            calendar.unselect();
        },
    });
}

function confirmReschedule(info, title, text, successText) {
    Swal.fire({
        title, text, icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#007bff',
    }).then(function (result) {
        if (!result.isConfirmed) { info.revert(); return; }

        const event = info.event;
        const props = event.extendedProps;
        const url = cfg.urls.updateTime + '/' + props.reservation_id;

        $.ajax({
            url,
            type: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                start_time: event.startStr,
                end_time: event.endStr,
            },
            success(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: successText, timer: 2000, showConfirmButton: false });
                } else {
                    info.revert();
                    Swal.fire({ icon: 'error', title: 'Gagal', text: response.message || 'Gagal memperbarui jadwal.' });
                }
            },
            error(xhr) {
                info.revert();
                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Gagal memperbarui jadwal.' });
            },
        });
    });
}

function esc(val) {
    const el = document.createElement('span');
    el.textContent = val;
    return el.innerHTML;
}

function formatDateTime(date) {
    return new Date(date).toLocaleString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

function formatTime(date) {
    return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

if (calendarEl) {
    const calendar = makeCalendar();
    calendar.render();

    $('#roomFilter').on('change', () => calendar.refetchEvents());
    $('input[type="checkbox"][id^="status"]').on('change', () => calendar.refetchEvents());

    $('#createReservationForm').on('submit', function (e) {
        e.preventDefault();
        const submitBtn = $('#createSubmitBtn');
        const originalText = submitBtn.html();
        const createFormErrors = $('#createFormErrors');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        createFormErrors.addClass('d-none').html('');

        $.ajax({
            url: cfg.urls.store,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            data: $(this).serialize(),
            success() {
                $('#createModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Reservasi berhasil dibuat.', timer: 2000, showConfirmButton: false });
                calendar.refetchEvents();
                $('#createReservationForm')[0].reset();
            },
            error(xhr) {
                const box = createFormErrors.removeClass('d-none');
                box.empty();
                box.append('<strong>Terjadi kesalahan:</strong>');
                box.append(buildErrorList(xhr.responseJSON || {}));
            },
            complete() {
                submitBtn.prop('disabled', false).html(originalText);
            },
        });
    });
}