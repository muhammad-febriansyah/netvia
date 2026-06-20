@props([
    'name',
    'label',
    'checked' => false,
    'disabled' => false,
])

<label class="flex cursor-pointer items-center gap-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only"
        @checked(old($name, $checked)) @disabled($disabled)>
    <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition peer-checked:bg-brand
        after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
    <span class="text-sm font-medium text-ink">{{ $label }}</span>
</label>
