@extends('adminlte::page')

@section('title', 'Uji Transisi Reservasi')

@section('content_header')
    <div>
        <h1 class="m-0">Uji Transisi Reservasi</h1>
        <div class="page-subtitle">Alat bantu testing alur status reservasi (backdate & auto-transition).</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-admin">
                <div class="card-header py-3">
                    <h3 class="card-title">Jalankan Auto-Transition</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm text-muted mb-3">
                        Menandai reservasi <strong>pending</strong> yang sudah lewat menjadi
                        <span class="badge badge-secondary">dibatalkan</span>, dan reservasi
                        <strong>approved</strong> yang sudah lewat menjadi
                        <span class="badge badge-primary">selesai</span>.
                    </p>
                    <form action="{{ route('admin.tools.reservation-debug.run') }}" method="POST" data-submit-guard
                        data-loading-text="Menjalankan...">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-play mr-1"></i> Jalankan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-admin">
                <div class="card-header">
                    <h3 class="card-title">Daftar Reservasi</h3>
                </div>

                <div class="card-body border-bottom py-2">
                    <form action="{{ route('admin.tools.reservation-debug') }}" method="GET">
                        <div class="row">
                            <div class="col-lg-4 col-md-5">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Semua Status</option>
                                    @foreach ($statuses as $s)
                                        <option value="{{ $s->value }}" @selected($filterStatus === $s->value)>
                                            {{ $s->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Pemohon</th>
                                <th>Ruangan</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $r)
                                <tr>
                                    <td class="text-monospace small">{{ $r->id }}</td>
                                    <td>{{ $r->user?->name ?? '-' }}</td>
                                    <td>{{ $r->room?->name ?? '-' }}</td>
                                    <td class="small">{{ $r->start_time_local?->format('d M Y H:i') }}</td>
                                    <td class="small">{{ $r->end_time_local?->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge
                                            @if($r->status === 'pending') badge-warning
                                            @elseif($r->status === 'approved') badge-success
                                            @elseif($r->status === 'completed') badge-primary
                                            @elseif($r->status === 'rejected') badge-danger
                                            @else badge-secondary @endif">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.tools.reservation-debug.backdate', $r->id) }}"
                                            method="POST" class="d-inline" data-submit-guard data-loading-text="...">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-xs">
                                                <i class="fas fa-backward mr-1"></i> Geser ke Masa Lalu
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ada reservasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    {{ $reservations->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
@endpush

@include('admin.partials.timezone_detector')
