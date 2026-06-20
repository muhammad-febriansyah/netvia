@php
    use App\Enums\NotifikasiJenis;
@endphp

<x-layouts.app title="Template Pesan">
    <x-slot:header>Template Pesan</x-slot:header>
    <x-slot:subheader>Edit isi reminder &amp; struk per jenis dan channel.</x-slot:subheader>

    <div class="mb-5 rounded-2xl border border-line bg-white p-4 text-sm">
        <span class="font-medium text-ink">Placeholder tersedia:</span>
        <span class="ml-1 flex flex-wrap gap-1.5">
            @foreach ($placeholders as $ph)
                <code class="rounded bg-canvas px-1.5 py-0.5 text-xs text-brand">{{ $ph }}</code>
            @endforeach
        </span>
    </div>

    <div class="space-y-5">
        @forelse ($templates as $template)
            @php($jenisLabel = NotifikasiJenis::tryFrom($template->jenis)?->label() ?? $template->jenis)
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-ink">{{ $jenisLabel }}</h2>
                        <span class="text-xs font-medium text-muted">{{ ucfirst($template->channel) }}</span>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $template->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('notifikasi.templateUpdate', $template) }}" class="space-y-4">
                    @csrf @method('PUT')

                    @if ($template->channel === 'email')
                        <div class="form-field">
                            <label class="mb-[7px] block text-[13px] font-medium text-ink">Subjek</label>
                            <input type="text" name="subject" value="{{ old('subject', $template->subject) }}"
                                class="form-input" placeholder="cth: Tagihan internet Anda">
                        </div>
                    @endif

                    <div class="form-field">
                        <label class="mb-[7px] block text-[13px] font-medium text-ink">Isi Pesan <span class="text-red-500">*</span></label>
                        <textarea name="body" rows="5" class="form-textarea" placeholder="Tulis isi pesan…">{{ old('body', $template->body) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="peer sr-only" @checked($template->is_active)>
                            <span class="relative h-6 w-11 flex-none rounded-full bg-slate-300 transition peer-checked:bg-brand
                                after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                            <span class="text-sm font-medium text-ink">Aktif</span>
                        </label>
                        <button type="submit"
                            class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-brand px-5 text-sm font-semibold text-white hover:bg-brand-dark">
                            <i data-lucide="save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-6 text-sm text-muted shadow-sm ring-1 ring-line">
                Belum ada template. Jalankan <code class="rounded bg-canvas px-1.5 py-0.5 text-xs">php artisan db:seed --class=MessageTemplateSeeder</code>.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        <a href="{{ route('notifikasi.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
            <i data-lucide="arrow-left"></i> Kembali ke log
        </a>
    </div>
</x-layouts.app>
