@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'help' => null,
])

<div class="form-field">
    @if ($label)
        <label for="{{ $name }}" class="mb-[7px] block text-[13px] font-medium text-ink">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-input', 'input-invalid' => $errors->has($name)]) }}>

    @error($name)
        <p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>
    @enderror

    @if ($help)
        <p class="mt-1.5 text-[12px] text-muted">{{ $help }}</p>
    @endif
</div>
