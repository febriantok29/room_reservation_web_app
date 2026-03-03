@props(['action', 'method' => 'POST', 'submitGuard' => true, 'loadingText' => 'Menyimpan...'])

<div class="card card-admin">
    <div class="card-body">
        <form action="{{ $action }}" method="{{ $method === 'GET' ? 'GET' : 'POST' }}"
            @if ($submitGuard) data-submit-guard data-loading-text="{{ $loadingText }}" @endif>
            @csrf
            @if ($method !== 'GET' && $method !== 'POST')
                @method($method)
            @endif

            {{ $slot }}
        </form>
    </div>
</div>
