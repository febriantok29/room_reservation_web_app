@extends('adminlte::page')

@section('title', 'Tambah Fasilitas')

@section('content_header')
    <div>
        <h1 class="m-0">Tambah Fasilitas</h1>
        <div class="page-subtitle">Tambahkan satu atau banyak fasilitas sekaligus untuk dipakai pada data ruangan.</div>
    </div>
@stop

@section('content')
    @include('admin.partials.flash_message')

    <x-form.card action="{{ route('admin.facilities.store') }}">
        <x-form.section title="Informasi Fasilitas">
            <x-form.row>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="facility-name-input">Nama Fasilitas <span class="text-danger">*</span></label>

                        <div class="input-group">
                            <input type="text" id="facility-name-input" class="form-control"
                                placeholder="Ketik nama lalu tekan Enter, atau pisahkan dengan koma"
                                autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="facility-add-btn">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>

                        <div id="facility-chips" class="d-flex flex-wrap mt-2" style="gap:.35rem;"></div>

                        <small class="text-muted d-block mt-1">
                            Tekan <kbd>Enter</kbd> atau pisahkan dengan koma untuk menambah. Contoh:
                            <em>Proyektor, Whiteboard, Video Conference</em>.
                        </small>

                        @error('names')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-form.row>
        </x-form.section>

        <x-form.actions back-url="{{ route('admin.facilities') }}" submit-text="Simpan Semua" />
    </x-form.card>
@stop

@push('js')
    @include('admin.partials.form_submit_guard_script')
    <script>
        (function() {
            const input = document.getElementById('facility-name-input');
            const chipsBox = document.getElementById('facility-chips');
            const addBtn = document.getElementById('facility-add-btn');
            const form = input.closest('form');
            let names = [];

            function render() {
                chipsBox.innerHTML = '';
                names.forEach(function(name, i) {
                    const chip = document.createElement('span');
                    chip.className = 'badge badge-primary';
                    chip.style.cssText = 'font-size:.9rem;padding:.45em .7em;';
                    chip.textContent = name;

                    const close = document.createElement('a');
                    close.href = 'javascript:void(0)';
                    close.className = 'ml-1';
                    close.style.cssText = 'color:#fff;text-decoration:none;font-weight:bold;';
                    close.textContent = '×';
                    close.addEventListener('click', function() {
                        names.splice(i, 1);
                        render();
                    });
                    chip.appendChild(close);
                    chipsBox.appendChild(chip);
                });
            }

            function addNames(raw) {
                let hasNew = false;
                raw.split(',').forEach(function(part) {
                    const name = part.trim();
                    if (name && names.indexOf(name) === -1) {
                        names.push(name);
                        hasNew = true;
                    }
                });
                if (hasNew) render();
            }

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addNames(input.value);
                    input.value = '';
                }
            });

            input.addEventListener('change', function() {
                if (input.value.indexOf(',') !== -1) {
                    addNames(input.value);
                    input.value = '';
                }
            });

            addBtn.addEventListener('click', function() {
                addNames(input.value);
                input.value = '';
                input.focus();
            });

            form.addEventListener('submit', function() {
                form.querySelectorAll('input[name="names[]"]').forEach(function(el) { el.remove(); });
                names.forEach(function(name) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'names[]';
                    hidden.value = name;
                    form.appendChild(hidden);
                });
            });
        })();
    </script>
@endpush

@include('admin.partials.timezone_detector')
