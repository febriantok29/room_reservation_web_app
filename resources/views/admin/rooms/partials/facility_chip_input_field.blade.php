{{--
    Pill-style checkbox group untuk memilih fasilitas ruangan.
    Variabel yang digunakan:
      - $allFacilities  : Collection Facility (dari parent view)
      - $selectedIds    : array UUID fasilitas yang sudah dipilih (pass via @include)
--}}
@php $selectedIds = (array) ($selectedIds ?? []); @endphp

<div class="form-group mb-0">
    <label class="d-block mb-1">Fasilitas</label>

    <div class="d-flex flex-wrap" data-toggle="buttons">
        @forelse ($allFacilities as $facility)
            @php $checked = in_array($facility->id, $selectedIds); @endphp
            <label class="btn btn-sm btn-outline-secondary mr-1 mb-1{{ $checked ? ' active' : '' }}">
                <input type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" autocomplete="off"
                    {{ $checked ? 'checked' : '' }}>
                {{ $facility->name }}
            </label>
        @empty
            <span class="text-muted small">
                Belum ada fasilitas. Silakan tambahkan melalui menu
                <a href="{{ route('admin.facilities') }}">Master Fasilitas</a>.
            </span>
        @endforelse
    </div>

    <small class="text-muted d-block mt-1">Opsional: Klik untuk memilih fasilitas yang tersedia di ruangan ini.</small>

    @error('facility_ids')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
