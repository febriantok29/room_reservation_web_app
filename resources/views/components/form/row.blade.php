@props([
    'class' => '',
])

<div class="row {{ $class }}">
    {{ $slot }}
</div>
