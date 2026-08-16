@extends('adminlte::page')

@section('title', 'Detail Komplain')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Detail Komplain</h1>
            <div class="page-subtitle">Lihat detail laporan dan perbarui status penanganan.</div>
        </div>
        <a href="{{ route('admin.complaints') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    @php
        $sColor = match ($complaint->status) {
            'open' => ['badge' => 'danger', 'border' => '#dc3545', 'icon' => 'fas fa-exclamation-circle'],
            'in_progress' => ['badge' => 'warning', 'border' => '#ffc107', 'icon' => 'fas fa-spinner'],
            'resolved' => ['badge' => 'success', 'border' => '#28a745', 'icon' => 'fas fa-check-circle'],
            'rejected' => ['badge' => 'secondary', 'border' => '#6c757d', 'icon' => 'fas fa-times-circle'],
            default => ['badge' => 'secondary', 'border' => '#6c757d', 'icon' => 'fas fa-circle'],
        };
        $sLabel = match ($complaint->status) {
            'open' => 'TERBUKA',
            'in_progress' => 'DALAM PROSES',
            'resolved' => 'DISELESAIKAN',
            'rejected' => 'DITOLAK',
            default => strtoupper($complaint->status),
        };
    @endphp

    <div class="row">

        {{-- Left column: complaint detail --}}
        <div class="col-lg-8">
            <div class="card card-admin" style="border-left:4px solid {{ $sColor['border'] }};">

                {{-- Header: ID + status --}}
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="text-muted small font-weight-bold mb-1">ID KOMPLAIN</div>
                        <div class="font-weight-bold" style="font-size:1.1rem;">{{ $complaint->id }}</div>
                    </div>
                    <span class="badge badge-{{ $sColor['badge'] }}" style="font-size:.85rem;padding:.45em .75em;">
                        <i class="{{ $sColor['icon'] }} mr-1"></i>{{ $sLabel }}
                    </span>
                </div>

                <div class="card-body">

                    {{-- Title --}}
                    <h5 class="font-weight-bold mb-3">{{ $complaint->title }}</h5>

                    {{-- Info grid --}}
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small mb-1"><i class="fas fa-user mr-1"></i>Pelapor</div>
                            <div class="font-weight-semibold">{{ $complaint->reporter?->full_name ?? '-' }}</div>
                            @if ($complaint->reporter?->employee_id)
                                <div class="text-muted small">NIK: {{ $complaint->reporter->employee_id }}</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small mb-1"><i class="fas fa-door-open mr-1"></i>Ruangan</div>
                            <div class="font-weight-semibold">{{ $complaint->room?->name ?? '-' }}</div>
                            @if ($complaint->room?->floor)
                                <div class="text-muted small">Lantai {{ $complaint->room->floor }}</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small mb-1"><i class="fas fa-plug mr-1"></i>Fasilitas Terkait</div>
                            <div class="font-weight-semibold">
                                {{ $complaint->facility?->name ?? '—' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small mb-1"><i class="fas fa-calendar-check mr-1"></i>ID Reservasi</div>
                            <div class="font-weight-semibold">{{ $complaint->reservation_id }}</div>
                            @if ($complaint->reservation)
                                <div class="text-muted small">
                                    {{ $complaint->reservation->start_time_local?->format('d M Y, H:i') }}
                                    &ndash;
                                    {{ $complaint->reservation->end_time_local?->format('H:i') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small mb-1"><i class="fas fa-clock mr-1"></i>Dilaporkan Pada</div>
                            <div class="font-weight-semibold">
                                {{ $complaint->created_at?->format('d M Y, H:i') ?? '-' }}
                            </div>
                        </div>
                        @if ($complaint->isClosed())
                            <div class="col-md-6 mb-3">
                                <div class="text-muted small mb-1"><i class="fas fa-user-shield mr-1"></i>Ditangani Oleh
                                </div>
                                <div class="font-weight-semibold">
                                    {{ $complaint->resolver?->full_name ?? '-' }}
                                </div>
                                @if ($complaint->resolved_at)
                                    <div class="text-muted small">
                                        {{ $complaint->resolved_at->format('d M Y, H:i') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <hr>

                    {{-- Description --}}
                    <div class="mb-3">
                        <div class="text-muted small font-weight-bold mb-1">
                            <i class="fas fa-align-left mr-1"></i>DESKRIPSI MASALAH
                        </div>
                        <div style="white-space:pre-wrap;line-height:1.6;">{{ $complaint->description }}</div>
                    </div>

                    {{-- Resolution notes (if any) --}}
                    @if ($complaint->resolution_notes)
                        <div class="alert alert-{{ $complaint->isResolved() ? 'success' : 'secondary' }} mt-3 mb-0">
                            <div class="font-weight-bold mb-1">
                                <i class="fas fa-clipboard-check mr-1"></i>
                                {{ $complaint->isResolved() ? 'Catatan Penyelesaian' : 'Alasan Penolakan' }}
                            </div>
                            <div style="white-space:pre-wrap;">{{ $complaint->resolution_notes }}</div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Photo section --}}
            @if ($complaint->photo_url)
                <div class="card card-admin mt-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-image mr-1"></i>Foto Bukti</h3>
                    </div>
                    <div class="card-body text-center">
                        <a href="{{ $complaint->photo_url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $complaint->photo_url }}" alt="Foto komplain"
                                style="max-width:100%;max-height:480px;object-fit:contain;border-radius:.375rem;cursor:zoom-in;">
                        </a>
                        <div class="text-muted small mt-2">Klik gambar untuk melihat ukuran penuh</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right column: status update form --}}
        <div class="col-lg-4">
            @unless ($complaint->isClosed())
                <div class="card card-admin card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tasks mr-1"></i>Perbarui Status</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.complaints.update-status', $complaint->id) }}" method="POST"
                            onsubmit="return confirm('Tutup komplain ini? Status tidak dapat diubah lagi.')"
                            data-submit-guard data-loading-text="Memproses...">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label for="status" class="font-weight-semibold">Status Baru <span
                                        class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="in_progress" @selected(old('status') === 'in_progress')>
                                        Dalam Proses
                                    </option>
                                    <option value="resolved" @selected(old('status') === 'resolved')>
                                        Diselesaikan
                                    </option>
                                    <option value="rejected" @selected(old('status') === 'rejected')>
                                        Ditolak
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="resolution_notes" class="font-weight-semibold">Catatan</label>
                                <textarea name="resolution_notes" id="resolution_notes" rows="5"
                                    class="form-control @error('resolution_notes') is-invalid @enderror"
                                    placeholder="Isi catatan penanganan atau alasan penolakan (opsional)...">{{ old('resolution_notes') }}</textarea>
                                @error('resolution_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Wajib diisi jika status Diselesaikan atau Ditolak.</small>
                            </div>

                            <div id="checkbox-maintenance" class="form-group mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="set_maintenance"
                                        name="set_maintenance" value="1" {{ old('set_maintenance') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="set_maintenance">
                                        Tandai ruangan sebagai <strong>Maintenance</strong>
                                    </label>
                                </div>
                                <small class="text-muted ml-4">Aktifkan jika ruangan perlu dihentikan sementara dari pemesanan
                                    untuk perbaikan.</small>
                            </div>

                            <div id="checkbox-unset-maintenance" class="form-group mb-2" style="display:none;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="unset_maintenance"
                                        name="unset_maintenance" value="1"
                                        {{ old('unset_maintenance') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="unset_maintenance">
                                        Tandai ruangan <strong>tidak Maintenance</strong>
                                    </label>
                                </div>
                                <small class="text-muted ml-4">Centang jika ruangan sudah selesai diperbaiki dan siap digunakan
                                    kembali.</small>
                            </div>

                            <button type="submit" class="btn btn-warning btn-block btn-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Status
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card card-admin card-outline card-{{ $complaint->isResolved() ? 'success' : 'secondary' }}">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="{{ $sColor['icon'] }} mr-1"></i>{{ $sLabel }}
                        </h3>
                    </div>
                    <div class="card-body text-muted text-sm">
                        Komplain ini sudah ditutup dan tidak dapat diubah statusnya lagi.
                    </div>
                </div>
            @endunless


        </div>

    </div>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
    <script>
        (function() {
            const statusEl = document.getElementById('status');
            const boxMaintenance = document.getElementById('checkbox-maintenance');
            const boxUnset = document.getElementById('checkbox-unset-maintenance');

            function toggle() {
                const isResolved = statusEl.value === 'resolved';
                boxMaintenance.style.display = isResolved ? 'none' : '';
                boxUnset.style.display = isResolved ? '' : 'none';
                if (!isResolved) document.getElementById('unset_maintenance').checked = false;
                if (isResolved) document.getElementById('set_maintenance').checked = false;
            }

            if (statusEl) {
                statusEl.addEventListener('change', toggle);
                toggle();
            }
        })();
    </script>
@endpush
