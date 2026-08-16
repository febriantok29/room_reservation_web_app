@props(['title', 'subtitle' => '', 'backUrl' => null, 'backText' => 'Kembali'])

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
        <div>
            <h1 class="m-0">{{ $title }}</h1>
            @if ($subtitle)
                <div class="page-subtitle">
                    @if ($backUrl)
                        <a href="{{ $backUrl }}" class="text-muted">
                            <i class="fas fa-arrow-left mr-1"></i> {{ $backText }}
                        </a>
                    @else
                        {{ $subtitle }}
                    @endif
                </div>
            @endif
        </div>
        @if ($slot->isNotEmpty())
            <div class="d-flex flex-wrap" style="gap:.5rem;">
                {{ $slot }}
            </div>
        @endif
    </div>
@stop
