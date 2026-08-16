@extends('adminlte::page')

@section('title', 'Log Kesalahan Sistem')

@section('content_header')
    <div>
        <h1 class="m-0">Log Kesalahan Sistem</h1>
        <div class="page-subtitle">Catatan error internal server yang perlu ditindaklanjuti.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin mb-3">
        <div class="card-body py-3">
            <form action="{{ route('admin.tools.error-logs') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="mb-1 text-sm font-weight-bold">Kode Error</label>
                        <input type="text" name="error_code" value="{{ $filterErrorCode }}" class="form-control form-control-sm"
                            placeholder="cth. QA7BA2">
                    </div>
                    <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                        <label class="mb-1 text-sm font-weight-bold">Endpoint</label>
                        <input type="text" name="endpoint" value="{{ $filterEndpoint }}" class="form-control form-control-sm"
                            placeholder="Bagian URL endpoint">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="mb-1 text-sm font-weight-bold">ID User</label>
                        <input type="text" name="user_id" value="{{ $filterUserId }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-lg-2 col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i>Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-admin">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Kode</th>
                        <th>Endpoint</th>
                        <th>User</th>
                        <th>Pesan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="small">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.tools.error-logs.show', $log) }}" class="text-monospace font-weight-bold">
                                    {{ $log->error_code }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-secondary mr-1">{{ $log->http_method }}</span>
                                <span class="small text-break">{{ $log->endpoint }}</span>
                            </td>
                            <td class="small">{{ $log->user_id ? Str::limit($log->user_id, 10) : '-' }}</td>
                            <td class="small text-muted text-truncate" style="max-width:240px;">{{ $log->message }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.tools.error-logs.show', $log) }}" class="btn btn-outline-primary btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada error yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $logs->links() }}
        </div>
    </div>
@stop