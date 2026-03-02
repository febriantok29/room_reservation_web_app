@extends('adminlte::page')

@section('title', 'Tambah Fasilitas')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Fasilitas</h1>
        <div class="page-subtitle">Tambahkan fasilitas baru agar bisa dipakai pada data ruangan.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-body">
            <form action="{{ route('admin.facilities.store') }}" method="POST" data-submit-guard
                data-loading-text="Menyimpan...">
                @csrf

                <div class="form-section-title">Informasi Fasilitas</div>

                <div class="form-group">
                    <label for="name">Nama Fasilitas</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                        required>
                    <small class="text-muted">Contoh: Proyektor, Whiteboard, Video Conference.</small>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.facilities') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop
