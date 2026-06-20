@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih…',
    'required' => false,
    'allowClear' => false,
    'help' => null,
])

@php
    $current = old($name, $selected);
@endphp

<div class="form-field">
    @if ($label)
        <label for="{{ $name }}" class="mb-[7px] block text-[13px] font-medium text-ink">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        data-placeholder="{{ $placeholder }}"
        @if ($allowClear) data-allow-clear="true" @endif
        @if ($required) required @endif
        {{ $attributes->class(['select2 form-select', 'input-invalid' => $errors->has($name)]) }}>
        <option value=""></option>
        @if (trim($slot))
            {{ $slot }}
        @else
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $text }}</option>
            @endforeach
        @endif
    </select>

    @error($name)
        <p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>
    @enderror

    @if ($help)
        <p class="mt-1.5 text-[12px] text-muted">{{ $help }}</p>
    @endif
</div>
