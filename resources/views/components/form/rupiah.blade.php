@props([
    'name',
    'label' => null,
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

    <div class="relative">
        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-medium text-muted">Rp</span>
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="text"
            inputmode="numeric"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            {{ $attributes->class(['form-input rupiah-mask !pl-9', 'input-invalid' => $errors->has($name)]) }}>
    </div>

    @error($name)
        <p class="field-error mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>
    @enderror

    @if ($help)
        <p class="mt-1.5 text-[12px] text-muted">{{ $help }}</p>
    @endif
</div>
