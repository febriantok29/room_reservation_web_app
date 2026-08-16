{{--
    Pill-style checkbox group untuk filter ruangan multi-select pada laporan.
    Variabel yang digunakan:
      - $rooms    : Collection Room
      - $selected : array room id yang sudah dipilih
--}}
@php $selected = (array) ($selected ?? []); @endphp

<div class="d-flex flex-wrap" data-toggle="buttons">
    @forelse ($rooms as $room)
        @php $checked = in_array($room->id, $selected); @endphp
        <label class="btn btn-sm btn-outline-secondary mr-1 mb-1{{ $checked ? ' active' : '' }}">
            <input type="checkbox" name="room_id[]" value="{{ $room->id }}" autocomplete="off"
                {{ $checked ? 'checked' : '' }}>
            {{ $room->name }} (Lt. {{ $room->floor }})
        </label>
    @empty
        <span class="text-muted small">Belum ada ruangan.</span>
    @endforelse
</div>
