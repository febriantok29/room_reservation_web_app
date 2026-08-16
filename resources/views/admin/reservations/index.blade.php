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
                            <input type="checkbox" id="statusRejected" value="rejected" checked>
                            <span class="badge badge-danger ml-1">Ditolak</span>
                        </label>
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusCompleted" value="completed" checked>
                            <span class="badge badge-primary ml-1">Selesai</span>
                        </label>
                        <label class="mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="statusCancelled" value="cancelled" checked>
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    @vite(['resources/css/admin/reservations.css'])
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        window.RapaCalendarConfig = {
            csrfToken: @json(csrf_token()),
            urls: {
                events: @json(route('admin.reservations.calendar.events')),
                store: @json(route('admin.reservations.store')),
                updateTime: @json(url('admin/reservations')) + '/',
                edit: @json(url('admin/reservations')) + '/',
                approve: @json(url('admin/approvals')) + '/',
                reject: @json(url('admin/approvals')) + '/',
                complete: @json(url('admin/reservations')) + '/',
            },
        };
    </script>
    @vite(['resources/js/admin/reservations.js'])
@endpush

@include('admin.partials.timezone_detector')
