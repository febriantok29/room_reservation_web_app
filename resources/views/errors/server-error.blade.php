@extends('adminlte::page')

@section('title', 'Terjadi Kesalahan')

@section('content_header')
    <div>
        <h1 class="m-0">Terjadi Kesalahan</h1>
    </div>
@stop

@section('content')
    <div class="card card-admin" style="max-width:600px;">
        <div class="card-body text-center py-5">
            <i class="fas fa-exclamation-triangle text-warning" style="font-size:3rem;"></i>
            <h4 class="mt-3">Maaf, terjadi kesalahan pada server</h4>
            <p class="text-muted mb-4">{{ $message }}</p>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home mr-1"></i> Kembali ke Dasbor
            </a>
        </div>
    </div>
@stop