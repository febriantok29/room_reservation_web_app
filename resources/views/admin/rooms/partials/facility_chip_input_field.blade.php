<div class="form-group">
    <label for="facility_ids_input">Fasilitas</label>
    <input type="hidden" id="facility_ids_input" name="facility_ids_input" value="{{ $hiddenValue ?? '' }}">

    <div class="border rounded p-2 position-relative" id="facility-chip-wrapper">
        <div id="facility-chip-list" class="mb-2 d-flex flex-wrap"></div>
        <input type="text" id="facility_input" class="form-control border-0 p-0"
            placeholder="Ketik fasilitas lalu tekan Enter">

        <div id="facility-suggestion-menu" class="list-group position-absolute w-100 shadow-sm d-none"
            style="z-index: 1050; top: calc(100% + 4px); left: 0;"></div>
    </div>

    <small class="text-muted">Ketik lalu Enter untuk tambah chip, atau klik suggestion yang muncul.</small>
</div>
