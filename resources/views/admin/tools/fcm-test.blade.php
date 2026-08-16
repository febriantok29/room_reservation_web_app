@extends('adminlte::page')

@section('title', 'Uji Notifikasi')

@section('content_header')
    <div>
        <h1 class="m-0">Uji Notifikasi</h1>
        <div class="page-subtitle">Kirim push notification manual untuk menguji jalur FCM tanpa membuat reservasi.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="card card-admin" style="max-width:600px;">
        <div class="card-header py-3">
            <h3 class="card-title">Form Kirim Notifikasi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.tools.fcm-test.send') }}" method="POST" data-submit-guard
                data-loading-text="Memproses...">
                @csrf

                <div class="form-group">
                    <label>Target <span class="text-danger">*</span></label>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="target_user" name="target" value="user" class="custom-control-input"
                            {{ old('target', 'user') === 'user' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="target_user">Satu user</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="target_all" name="target" value="all" class="custom-control-input"
                            {{ old('target') === 'all' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="target_all">Semua user bertoken ({{ $users->count() }} user)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="user_id">User Tujuan</label>
                    <select id="user_id" name="user_id" class="form-control @error('user_id') is-invalid @enderror">
                        <option value="">— pilih user —</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') === $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }} ({{ $user->employee_id }}) — {{ $user->fcm_tokens_count }} device
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="title">Judul <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', 'Test Notifikasi') }}" maxlength="100" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="body">Isi Pesan <span class="text-danger">*</span></label>
                    <textarea id="body" name="body" rows="3" maxlength="500"
                        class="form-control @error('body') is-invalid @enderror"
                        required>{{ old('body', 'Ini adalah notifikasi percobaan dari web admin RapaTrack.') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="to_database" name="to_database" value="1" class="custom-control-input"
                            {{ old('to_database') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="to_database">Simpan juga ke daftar notifikasi user (muncul di list mobile)</label>
                    </div>
                </div>

                @error('send')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-1"></i> Kirim
                </button>
            </form>
        </div>
    </div>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
    <script>
        const userSelect = document.getElementById('user_id');
        const syncTarget = () => {
            userSelect.disabled = document.getElementById('target_all').checked;
        };
        document.querySelectorAll('input[name="target"]').forEach(r => r.addEventListener('change', syncTarget));
        syncTarget();
    </script>
@endpush
