@extends('adminlte::page')

@section('title', 'Detail Log Error')

@section('content_header')
    <div>
        <h1 class="m-0">Detail Log Error</h1>
        <div class="page-subtitle">
            <a href="{{ route('admin.tools.error-logs') }}" class="text-muted">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-5">
            <div class="card card-admin">
                <div class="card-header py-3">
                    <h3 class="card-title">Informasi Umum</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="w-50 text-muted">Kode Error</th>
                                <td><span class="text-monospace font-weight-bold">{{ $log->error_code }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Waktu</th>
                                <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Method</th>
                                <td><span class="badge badge-secondary">{{ $log->http_method }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Endpoint</th>
                                <td class="small text-break">{{ $log->endpoint }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">User ID</th>
                                <td>{{ $log->user_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Exception</th>
                                <td class="small text-break">{{ $log->exception_class ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-admin">
                <div class="card-header py-3">
                    <h3 class="card-title">Detail Teknis</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Pesan:</strong></p>
                    <pre class="bg-light p-3 rounded" style="white-space:pre-wrap;">{{ $log->message }}</pre>

                    @if ($log->request_body)
                        <p class="mb-2 mt-4"><strong>Request Body (disensor):</strong></p>
                        <pre class="bg-light p-3 rounded" style="white-space:pre-wrap;">{{ $log->request_body }}</pre>
                    @endif

                    @if ($log->stack_trace)
                        <p class="mb-2 mt-4"><strong>Stack Trace:</strong></p>
                        <pre class="bg-light p-3 rounded" style="white-space:pre-wrap;">{{ $log->stack_trace }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop