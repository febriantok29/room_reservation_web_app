@extends('adminlte::page')

@section('title', 'Ubah Fasilitas')

@section('content_header')
    <div>
        <h1 class="m-0">Ubah Fasilitas</h1>
        <div class="page-subtitle">Perbarui nama fasilitas agar konsisten dipakai di seluruh data ruangan.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin">
        <div class="card-body">
            <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST" data-submit-guard
                data-loading-text="Memperbarui...">
                @csrf
                @method('PUT')

                <div class="form-section-title">Informasi Fasilitas</div>

                <div class="form-group">
                    <label for="name">Nama Fasilitas</label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name', $facility->name) }}" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.facilities') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    @include('admin.partials.form_submit_guard_script')
@stop
