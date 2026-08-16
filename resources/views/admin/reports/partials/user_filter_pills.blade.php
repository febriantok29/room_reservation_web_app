{{--
    Pill-style checkbox group untuk filter karyawan multi-select pada laporan.
    Variabel yang digunakan:
      - $users    : Collection User
      - $selected : array user id yang sudah dipilih
--}}
@php $selected = (array) ($selected ?? []); @endphp

<div class="d-flex flex-wrap" data-toggle="buttons">
    @forelse ($users as $user)
        @php $checked = in_array($user->id, $selected); @endphp
        <label class="btn btn-sm btn-outline-secondary mr-1 mb-1{{ $checked ? ' active' : '' }}">
            <input type="checkbox" name="user_id[]" value="{{ $user->id }}" autocomplete="off"
                {{ $checked ? 'checked' : '' }}>
            {{ $user->full_name }} ({{ $user->employee_id }})
        </label>
    @empty
        <span class="text-muted small">Belum ada karyawan.</span>
    @endforelse
</div>
