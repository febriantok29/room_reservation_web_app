@extends('adminlte::page')

@section('title', 'Ganti Password')

@section('content_header')
    <div>
        <h1 class="m-0">Ganti Password</h1>
        <div class="page-subtitle">Anda wajib mengganti password sebelum melanjutkan.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin" style="max-width:500px;">
        <div class="card-header py-3">
            <h3 class="card-title">Form Ganti Password</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.password.change.submit') }}" method="POST" data-submit-guard
                data-loading-text="Menyimpan...">
                @csrf

                <div class="form-group">
                    <label for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="6"
                        required>
                    <small class="form-text text-muted">Minimal 6 karakter.</small>
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Ulangi Password Baru <span class="text-danger">*</span></label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                        class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key mr-1"></i> Ganti Password
                </button>
            </form>
        </div>
    </div>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
@endpush
