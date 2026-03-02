@extends('adminlte::page')

@section('title', 'Ubah Fasilitas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Ubah Fasilitas</h1>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST">
                @csrf
                @method('PUT')

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
