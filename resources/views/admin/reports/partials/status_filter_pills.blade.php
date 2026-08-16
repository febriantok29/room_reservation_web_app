{{--
    Pill-style checkbox group untuk filter status multi-select pada laporan.
    Variabel yang digunakan:
      - $options  : array asosiatif value => label
      - $selected : array value yang sudah dipilih
      - $name     : nama field form (default 'status')
--}}
@php
    $selected = (array) ($selected ?? []);
    $name = $name ?? 'status';
@endphp

<div class="d-flex flex-wrap" data-toggle="buttons">
    @foreach ($options as $value => $label)
        @php $checked = in_array($value, $selected); @endphp
        <label class="btn btn-sm btn-outline-secondary mr-1 mb-1{{ $checked ? ' active' : '' }}">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" autocomplete="off"
                {{ $checked ? 'checked' : '' }}>
            {{ $label }}
        </label>
    @endforeach
</div>
