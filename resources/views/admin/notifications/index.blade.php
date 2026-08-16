@extends('adminlte::page')

@section('title', 'Notifikasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">Notifikasi</h1>
            <div class="page-subtitle">Notifikasi reservasi dan keluhan untuk admin.</div>
        </div>
        @if($unreadCount > 0)
            <button type="button" class="btn btn-primary btn-sm" id="mark-all-read">
                <i class="fas fa-check-double"></i> Tandai Semua Dibaca
            </button>
        @endif
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-body border-bottom py-2 text-sm text-muted">
            Menampilkan {{ $notifications->count() }} dari {{ $notifications->total() }} notifikasi.
            @if($unreadCount > 0)
                · <strong class="text-danger">{{ $unreadCount }} belum dibaca</strong>
            @endif
        </div>

        <div class="card-body table-responsive p-0">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isRead = $notification->read_at !== null;
                    $title = $data['title'] ?? 'Notifikasi';
                    $body = $data['body'] ?? '';
                    $type = $data['type'] ?? 'general';
                    $reservationId = $data['reservation_id'] ?? null;
                    $complaintId = $data['complaint_id'] ?? null;
                    $link = null;
                    if ($complaintId) {
                        $link = route('admin.complaints');
                    } elseif ($reservationId) {
                        $link = route('admin.reservations');
                    }
                @endphp
                <div class="d-flex align-items-start px-3 py-3 border-bottom {{ $isRead ? '' : 'bg-light' }}">
                    <div class="mr-3">
                        @if(!$isRead)
                            <span class="badge badge-danger"><i class="fas fa-circle"></i></span>
                        @else
                            <span class="badge badge-secondary"><i class="far fa-circle"></i></span>
                        @endif
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">{{ $title }}</span>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="small text-muted">{{ $body }}</div>
                    </div>
                    <div class="ml-3 text-right">
                        @if(!$isRead)
                            <button type="button" class="btn btn-outline-primary btn-xs mark-read"
                                data-id="{{ $notification->id }}">
                                <i class="fas fa-check"></i> Tandai Dibaca
                            </button>
                        @endif
                        @if($link)
                            <a href="{{ $link }}" class="btn btn-outline-secondary btn-xs">
                                <i class="fas fa-external-link-alt"></i> Lihat
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-bell-slash fa-2x d-block mb-2"></i>
                    Belum ada notifikasi.
                </div>
            @endforelse
        </div>

        <div class="card-footer clearfix">
            {{ $notifications->links() }}
        </div>
    </div>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mark-read').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = btn.dataset.id;
                    fetch('{{ route('admin.notifications.read', ':id') }}'.replace(':id', id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    }).then(() => window.location.reload());
                });
            });

            const markAllBtn = document.getElementById('mark-all-read');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function() {
                    fetch('{{ route('admin.notifications.read-all') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    }).then(() => window.location.reload());
                });
            }
        });
    </script>
@endpush

@include('admin.partials.timezone_detector')
