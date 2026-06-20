@props([
    'icon',
    'title',
    'desc' => null,
])

<div class="flex items-start gap-3.5 border-b border-line p-6">
    <span class="flex size-11 flex-none items-center justify-center rounded-xl bg-brand-soft text-brand">
        <i data-lucide="{{ $icon }}" class="size-5"></i>
    </span>
    <div class="min-w-0">
        <h2 class="text-base font-semibold text-ink">{{ $title }}</h2>
        @if ($desc)
            <p class="mt-0.5 text-[13px] text-muted">{{ $desc }}</p>
        @endif
    </div>
</div>
