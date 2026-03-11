{{--
    Room image upload partial.
    Variables:
      $room        (optional) — Room model for edit mode. Null on create.
      $deleteRoute (optional) — Route string for the DELETE image action (edit only).
--}}
@php
    $hasImage = isset($room) && $room->image_path;
    $previewSrc = $hasImage ? $room->image_url : asset('images/not_available.jpg');
@endphp

{{-- Preview box --}}
<div class="border rounded overflow-hidden mb-2" style="height:210px; background:#f4f6f9;">
    <img id="room-img-preview" src="{{ $previewSrc }}" alt="Preview foto ruangan" class="w-100 h-100"
        style="object-fit:cover;{{ $hasImage ? '' : 'opacity:.55;' }}">
</div>

{{-- File picker --}}
<div class="custom-file mb-2">
    <input type="file" name="image" id="room-image-input"
        class="custom-file-input @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp"
        onchange="roomImagePreview(this)">
    <label class="custom-file-label text-truncate pr-5" id="room-image-label" for="room-image-input">
        {{ $hasImage ? 'Pilih foto pengganti...' : 'Pilih gambar...' }}
    </label>
    @error('image')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<small class="form-text text-muted">
    Format: JPG, PNG, WEBP &middot; Maks. 10 MB.
    File di atas 2 MB dikompres otomatis.
</small>

{{-- Delete button (edit only, when image exists) --}}
@if ($hasImage && isset($deleteRoute))
    <form action="{{ $deleteRoute }}" method="POST" class="mt-2"
        onsubmit="return confirm('Hapus foto ruangan ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger btn-block">
            <i class="fas fa-trash-alt mr-1"></i> Hapus Foto
        </button>
    </form>
@endif

<script>
    function roomImagePreview(input) {
        document.getElementById('room-image-label').textContent =
            input.files[0] ? input.files[0].name : 'Pilih gambar...';
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('room-img-preview');
                img.src = e.target.result;
                img.style.opacity = '1';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
