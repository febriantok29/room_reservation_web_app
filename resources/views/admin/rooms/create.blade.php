@extends('adminlte::page')

@section('title', 'Tambah Ruangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Tambah Ruangan</h1>
        @include('admin.partials.logout_button')
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.rooms.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="name">Nama Ruangan</label>
                            <input type="text" id="name" name="name" class="form-control"
                                value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="location">Lokasi</label>
                            <input type="text" id="location" name="location" class="form-control"
                                value="{{ old('location') }}" required>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="capacity">Kapasitas</label>
                            <input type="number" id="capacity" name="capacity" class="form-control"
                                value="{{ old('capacity', 1) }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" id="is_maintenance" name="is_maintenance" value="1" class="form-check-input"
                        @checked(old('is_maintenance'))>
                    <label class="form-check-label" for="is_maintenance">Sedang maintenance</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.rooms') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@stop
