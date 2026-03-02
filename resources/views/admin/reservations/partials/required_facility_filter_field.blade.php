<div class="form-group" id="required-facility-wrapper">
    <label>Filter Fasilitas Ruangan (opsional)</label>
    <input type="hidden" id="required_facilities_input" name="required_facilities_input" value="{{ $hiddenValue ?? '' }}">

    <div id="required-facility-chip-list" class="mb-2 d-flex flex-wrap"></div>

    <button type="button" id="required-facility-toggle" class="btn btn-outline-secondary btn-sm">
        Tampilkan Pilihan Fasilitas
    </button>

    <div id="required-facility-selector" class="border rounded p-2 mt-2 d-none">
        <div id="required-facility-options" class="row"></div>
    </div>

    <small class="text-muted d-block">Pilih satu atau lebih fasilitas untuk memfilter daftar
        ruangan.</small>
    <small id="required-facility-meta" class="text-muted d-block"></small>
</div>
