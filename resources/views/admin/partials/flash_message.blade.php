@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i>
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi kesalahan ({{ $errors->count() }}):</strong>
        <ul class="mb-0 mt-1">
            @foreach (array_slice($errors->all(), 0, 5) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        @if ($errors->count() > 5)
            <small class="d-block mt-1">Dan {{ $errors->count() - 5 }} kesalahan lainnya.</small>
        @endif
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
