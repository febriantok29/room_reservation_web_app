@extends('adminlte::page')

@section('title', 'Komplain & Kerusakan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Komplain & Kerusakan</h1>
            <div class="page-subtitle">Pantau dan tangani laporan kerusakan serta komplain fasilitas dari pengguna.</div>
        </div>
        <a href="{{ route('admin.complaints.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Komplain
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    {{-- Filter Bar --}}
    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form action="{{ route('admin.complaints') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-6 mb-2 mb-lg-0">
                        <input type="text" name="q" class="form-control form-control-sm"
                            placeholder="Cari ID, judul, nama pelapor, atau ruangan..." value="{{ $searchQuery ?? '' }}">
                    </div>
                    <div class="col-lg-4 col-md-4 mb-2 mb-lg-0">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="open" @selected(($statusFilter ?? '') === 'open')>Terbuka</option>
                            <option value="in_progress" @selected(($statusFilter ?? '') === 'in_progress')>Dalam Proses</option>
                            <option value="resolved" @selected(($statusFilter ?? '') === 'resolved')>Diselesaikan</option>
                            <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer py-2 text-sm text-muted">
            Menampilkan {{ $complaints->count() }} dari {{ $complaints->total() }} komplain.
        </div>
    </div>

    {{-- Complaint Cards --}}
    <div class="row">
        @forelse ($complaints as $complaint)
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
            <div class="col-xl-6 col-12 mb-3">
                <div class="card card-admin h-100" style="border-left:4px solid {{ $sColor['border'] }};">

                    {{-- Card Header --}}
                    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted small font-weight-bold">
                            <i class="fas fa-hashtag mr-1" style="font-size:.7rem;"></i>{{ $complaint->id }}
                        </span>
                        <span class="badge badge-{{ $sColor['badge'] }}">
                            <i class="{{ $sColor['icon'] }} mr-1"></i>{{ $sLabel }}
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body py-3 px-3">

                        {{-- Title --}}
                        <div class="font-weight-bold mb-2" style="font-size:.95rem;">
                            {{ $complaint->title }}
                        </div>

                        {{-- Room --}}
                        <div class="d-flex align-items-center mb-2 text-sm">
                            <i class="fas fa-door-open text-muted mr-2" style="width:16px;flex-shrink:0;"></i>
                            <span>{{ $complaint->room?->name ?? '-' }}</span>
                            @if ($complaint->room?->floor)
                                <span class="text-muted ml-2">· Lantai {{ $complaint->room->floor }}</span>
                            @endif
                        </div>

                        {{-- Facility (if any) --}}
                        @if ($complaint->facility)
                            <div class="d-flex align-items-center mb-2 text-sm">
                                <i class="fas fa-plug text-muted mr-2" style="width:16px;flex-shrink:0;"></i>
                                <span>{{ $complaint->facility->name }}</span>
                            </div>
                        @endif

                        {{-- Reporter --}}
                        <div class="d-flex align-items-center text-sm">
                            <i class="fas fa-user text-muted mr-2" style="width:16px;flex-shrink:0;"></i>
                            <span>{{ $complaint->reporter?->full_name ?? '-' }}</span>
                            @if ($complaint->reporter?->employee_id)
                                <span class="text-muted ml-2">· {{ $complaint->reporter->employee_id }}</span>
                            @endif
                        </div>

                        {{-- Reported at --}}
                        <div class="d-flex align-items-center mt-2 text-sm text-muted">
                            <i class="fas fa-clock mr-2" style="width:16px;flex-shrink:0;"></i>
                            <span>{{ $complaint->created_at?->format('d M Y, H:i') }}</span>
                        </div>

                        @if ($complaint->photo_url)
                            <div class="mt-2">
                                <span class="badge badge-light border text-secondary" style="font-size:.7rem;">
                                    <i class="fas fa-image mr-1"></i>Ada foto
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Card Footer --}}
                    <div class="card-footer bg-white border-top pt-2 pb-2 px-3">
                        <div class="table-action-group">
                            <a href="{{ route('admin.complaints.show', $complaint->id) }}" class="btn btn-info btn-xs">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @unless ($complaint->isClosed())
                                <a href="{{ route('admin.complaints.show', $complaint->id) }}" class="btn btn-warning btn-xs">
                                    <i class="fas fa-tasks"></i> Tangani
                                </a>
                            @endunless
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-admin">
                    <div class="card-body">
                        <div class="empty-state-cell" style="border:none;">
                            <div class="empty-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="empty-title">Belum ada data komplain</div>
                            <div class="empty-desc">Komplain dari pengguna akan muncul di sini.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($complaints->hasPages())
        <div class="mt-2">
            {{ $complaints->links() }}
        </div>
    @endif
@stop
