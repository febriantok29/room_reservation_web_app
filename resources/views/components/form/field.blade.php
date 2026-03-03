@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'hint' => '',
    'rows' => 3,
    'options' => [], // For select
    'multiple' => false, // For select
    'min' => null,
    'max' => null,
    'colClass' => 'col-md-6', // Responsive column class
])

<div class="{{ $colClass }}">
    <div class="form-group">
        <label for="{{ $name }}">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>

        @if ($slot->isNotEmpty())
            {{-- Custom field content via slot --}}
            {{ $slot }}
        @elseif ($type === 'textarea')
            <textarea id="{{ $name }}" name="{{ $name }}" class="form-control" rows="{{ $rows }}"
                placeholder="{{ $placeholder }}" @if ($required) required @endif
                @if ($disabled) disabled @endif>{{ old($name, $value) }}</textarea>
        @elseif($type === 'select')
            <select id="{{ $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" class="form-control"
                @if ($multiple) multiple @endif @if ($required) required @endif
                @if ($disabled) disabled @endif>
                @foreach ($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected(old($name, $value) == $optValue)>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
                class="form-control" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}"
                @if ($required) required @endif @if ($disabled) disabled @endif
                @if ($min !== null) min="{{ $min }}" @endif
                @if ($max !== null) max="{{ $max }}" @endif>
        @endif

        @if ($hint)
            <small class="text-muted">{{ $hint }}</small>
        @endif

        @error($name)
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
