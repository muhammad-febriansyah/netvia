@props([
    'target',
    'route',
    'field',
    'placeholder' => 'Tujuan tes',
])

<div class="flex items-center gap-2">
    <input type="text" id="tes-{{ $target }}-field" placeholder="{{ $placeholder }}"
        class="form-input !h-11 max-w-[220px]">
    <button type="button" id="tes-{{ $target }}-btn"
        class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-line bg-white px-4 text-sm font-medium text-ink transition hover:bg-canvas">
        <i data-lucide="send" class="size-4"></i>
        Tes Kirim
    </button>
</div>

@push('scripts')
<script>
    $(function () {
        $('#tes-{{ $target }}-btn').on('click', function () {
            const btn = $(this);
            btn.prop('disabled', true);
            $.post('{{ $route }}', { {{ $field }}: $('#tes-{{ $target }}-field').val() })
                .done((r) => Netvia.toast(r.message, 'success'))
                .fail((xhr) => Netvia.ajaxError(xhr))
                .always(() => btn.prop('disabled', false));
        });
    });
</script>
@endpush
