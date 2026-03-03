@props(['backUrl', 'backText' => 'Kembali', 'submitText' => 'Simpan', 'submitClass' => 'btn-primary'])

<div class="d-flex justify-content-between mt-4">
    <a href="{{ $backUrl }}" class="btn btn-secondary">{{ $backText }}</a>
    <button type="submit" class="btn {{ $submitClass }}">{{ $submitText }}</button>
</div>
