{{--
    Pill toggle buttons untuk memfilter daftar ruangan berdasarkan fasilitas.
    Tidak di-submit ke server — hanya digunakan untuk filter JS.
    Variabel yang digunakan:
      - $allFacilities : Collection Facility (dari parent view)
--}}
<div class="form-group mb-0">
    <label class="d-block mb-1"><i class="fas fa-filter mr-1"></i>Filter Fasilitas Ruangan</label>

    <div class="d-flex flex-wrap" data-toggle="buttons" id="facility-filter-group">
        @forelse ($allFacilities as $facility)
            <label class="btn btn-sm btn-outline-info mr-1 mb-1">
                <input type="checkbox" data-slug="{{ $facility->slug }}" autocomplete="off">
                {{ $facility->name }}
            </label>
        @empty
            <span class="text-muted small">Belum ada fasilitas terdaftar.</span>
        @endforelse
    </div>

    <small class="text-muted d-block mt-1">
        Opsional: Klik untuk memfilter ruangan berdasarkan fasilitas yang dibutuhkan.
        <span id="room-count-label" class="ml-1 font-weight-semibold"></span>
    </small>
</div>
