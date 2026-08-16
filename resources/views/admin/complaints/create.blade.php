@extends('adminlte::page')

@section('title', 'Tambah Komplain')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Komplain</h1>
        <div class="page-subtitle">Catat laporan kerusakan atau komplain fasilitas atas nama pengguna.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <div class="row">

        {{-- Main Form --}}
        <div class="col-lg-12">
            <x-form.card action="{{ route('admin.complaints.store') }}" submit-guard loading-text="Menyimpan..."
                :multipart="true">

                <x-form.section title="Referensi Reservasi" />

                <x-form.field name="reservation_id" label="Reservasi" required col-class="col-md-12">
                    <select id="reservation_id" name="reservation_id" class="form-control" required>
                        <option value="">-- Pilih Reservasi --</option>
                        @foreach ($reservations as $reservation)
                            <option value="{{ $reservation->id }}" data-room-id="{{ $reservation->room_id }}"
                                @selected(old('reservation_id') === $reservation->id)>
                                {{ $reservation->id }}
                                &mdash; {{ $reservation->room?->name ?? '-' }}
                                ({{ $reservation->start_time_local?->format('d M Y') }})
                                &mdash; {{ $reservation->user?->full_name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hanya reservasi berstatus <strong>Selesai</strong> yang dapat dikomplain —
                        komplain dilaporkan setelah ruangan selesai digunakan.</small>
                </x-form.field>

                <x-form.field name="facility_id" label="Fasilitas Terkait" col-class="col-md-12">
                    <select id="facility_id" name="facility_id" class="form-control">
                        <option value="">-- Tidak ada / Ruangan secara umum --</option>
                        @foreach ($facilities as $facility)
                            <option value="{{ $facility->id }}" @selected(old('facility_id') === $facility->id)>
                                {{ $facility->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Opsional: pilih jika komplain menyangkut fasilitas tertentu.</small>
                </x-form.field>

                <x-form.section title="Detail Komplain" />

                <x-form.field name="title" label="Judul Komplain" required col-class="col-md-12">
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}"
                        placeholder="Contoh: Proyektor tidak dapat menyala" maxlength="200" required>
                </x-form.field>

                <x-form.field name="description" label="Deskripsi Masalah" required col-class="col-md-12">
                    <textarea id="description" name="description" rows="5" class="form-control"
                        placeholder="Jelaskan masalah secara detail: kondisi yang terjadi, dampaknya, dan informasi relevan lainnya."
                        maxlength="2000" required>{{ old('description') }}</textarea>
                    <small class="text-muted">Maksimal 2.000 karakter.</small>
                </x-form.field>

                <x-form.section title="Foto Bukti" />

                <x-form.field name="photo" label="Foto" col-class="col-md-12">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="photo" name="photo"
                            accept=".jpg,.jpeg,.png,.webp">
                        <label class="custom-file-label" for="photo">Pilih file gambar...</label>
                    </div>
                    <small class="text-muted">
                        Opsional. Format: JPG, JPEG, PNG, atau WebP. Maks. 10 MB.
                        File di atas 2 MB akan dikompres otomatis.
                    </small>
                </x-form.field>

                <x-form.actions back-url="{{ route('admin.complaints') }}" submit-text="Simpan Komplain" />
            </x-form.card>
        </div>

    </div>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
    <script>
        // Update custom file input label with selected filename
        document.getElementById('photo')?.addEventListener('change', function() {
            const label = this.nextElementSibling;
            label.textContent = this.files[0]?.name ?? 'Pilih file gambar...';
        });
    </script>
@endpush
