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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nama Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                value="{{ old('name', $facility->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Slug (Auto-generated)</label>
                            <input type="text" class="form-control" value="{{ $facility->slug }}" disabled>
                            <small class="text-muted">Slug dihasilkan otomatis dari nama fasilitas.</small>
                        </div>
                    </div>
                </div>

                @if ($facility->rooms->isNotEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Fasilitas ini digunakan di <strong>{{ $facility->rooms->count() }} ruangan</strong>. Perubahan nama
                        akan mempengaruhi tampilan di semua ruangan tersebut.
                    </div>
                @endif

                <div class="d-flex justify-content-between mt-4">
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
