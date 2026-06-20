@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'rows' => 3,
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

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-textarea', 'input-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>
    @enderror

    @if ($help)
        <p class="mt-1.5 text-[12px] text-muted">{{ $help }}</p>
    @endif
</div>
