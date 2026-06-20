@php
    use App\Enums\TagihanStatus;
    $payable = in_array($tagihan->status, [TagihanStatus::Unpaid, TagihanStatus::Overdue], true);
@endphp

<x-layouts.app title="Detail Tagihan">
    <x-slot:header>{{ $tagihan->nomor_tagihan }}</x-slot:header>
    <x-slot:subheader>{{ $tagihan->pelanggan?->nama }} · {{ $tagihan->periode->translatedFormat('F Y') }}</x-slot:subheader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Detail + pembayaran --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-ink">Informasi Tagihan</h2>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $tagihan->status->badgeClass() }}">
                        {{ $tagihan->status->label() }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted">Pelanggan</dt>
                        <dd class="mt-0.5 font-medium">
                            <a href="{{ route('pelanggan.show', $tagihan->pelanggan_id) }}" class="text-brand hover:underline">
                                {{ $tagihan->pelanggan?->nama }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted">Paket</dt>
                        <dd class="mt-0.5 font-medium">{{ $tagihan->paket_nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Jumlah Tagihan</dt>
                        <dd class="mt-0.5 text-lg font-semibold">{{ rupiah($tagihan->jumlah) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Periode</dt>
                        <dd class="mt-0.5 font-medium">{{ $tagihan->periode->translatedFormat('F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Tanggal Terbit</dt>
                        <dd class="mt-0.5 font-medium">{{ $tagihan->tanggal_terbit->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Jatuh Tempo</dt>
                        <dd class="mt-0.5 font-medium">{{ $tagihan->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</dd>
                    </div>
                    @if ($tagihan->paid_at)
                        <div>
                            <dt class="text-muted">Dibayar Pada</dt>
                            <dd class="mt-0.5 font-medium">{{ $tagihan->paid_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($tagihan->status === TagihanStatus::Void && $tagihan->void_reason)
                        <div class="sm:col-span-2">
                            <dt class="text-muted">Alasan Pembatalan</dt>
                            <dd class="mt-0.5 font-medium text-red-600">{{ $tagihan->void_reason }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 flex flex-wrap gap-2 border-t border-line pt-5">
                    <a href="{{ $tagihan->publicUrl() }}" target="_blank"
                        class="inline-flex h-9 items-center gap-1.5 rounded-[10px] border border-line px-3.5 text-[13px] font-medium text-ink hover:bg-canvas">
                        <i data-lucide="external-link"></i> Halaman Publik
                    </a>
                    @if ($tagihan->status === TagihanStatus::Paid)
                        <a href="{{ route('publik.struk', $tagihan->public_token) }}" target="_blank"
                            class="inline-flex h-9 items-center gap-1.5 rounded-[10px] border border-line px-3.5 text-[13px] font-medium text-ink hover:bg-canvas">
                            <i data-lucide="download"></i> Unduh Struk
                        </a>
                    @endif
                </div>
            </div>

            {{-- Riwayat pembayaran --}}
            <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
                <h2 class="mb-5 text-sm font-semibold text-ink">Riwayat Pembayaran</h2>
                @forelse ($tagihan->pembayarans as $bayar)
                    <div class="flex items-center justify-between border-b border-line py-3 last:border-0">
                        <div>
                            <div class="text-sm font-medium">{{ $bayar->metode->label() }}</div>
                            <div class="text-xs text-muted">
                                {{ $bayar->dibayar_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') ?? $bayar->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}
                                @if ($bayar->dikonfirmasiBy)
                                    · oleh {{ $bayar->dikonfirmasiBy->name }}
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold">{{ rupiah($bayar->jumlah_bayar) }}</div>
                            <div class="text-xs text-muted">{{ $bayar->status->label() }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-muted">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </div>

        {{-- Aksi --}}
        <div class="space-y-6">
            @if ($payable)
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
                    <h2 class="mb-4 text-sm font-semibold text-ink">Aksi</h2>
                    <div class="space-y-2.5">
                        @can('pembayaran.create_qris')
                            <button type="button" id="btn-qris"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] bg-brand px-4 text-sm font-semibold text-white hover:bg-brand-dark">
                                <i data-lucide="qr-code"></i> Buat QRIS
                            </button>
                        @endcan
                        @can('pembayaran.konfirmasi')
                            <button type="button" id="btn-manual-toggle"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] border border-line px-4 text-sm font-medium text-ink hover:bg-canvas">
                                <i data-lucide="check-circle"></i> Tandai Lunas Manual
                            </button>
                        @endcan
                        @can('notifikasi.kirim')
                            <button type="button" id="btn-reminder"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] border border-line px-4 text-sm font-medium text-ink hover:bg-canvas">
                                <i data-lucide="bell"></i> Kirim Reminder
                            </button>
                        @endcan
                        @can('tagihan.void')
                            <button type="button" id="btn-void"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50">
                                <i data-lucide="x-circle"></i> Batalkan (Void)
                            </button>
                        @endcan
                    </div>

                    @can('pembayaran.konfirmasi')
                        {{-- Manual confirmation form --}}
                        <form id="form-manual" action="{{ route('pembayaran.konfirmasiManual', $tagihan) }}"
                            method="POST" enctype="multipart/form-data" class="mt-5 hidden space-y-3 border-t border-line pt-5">
                            @csrf
                            <div class="form-field">
                                <label class="mb-[7px] block text-[13px] font-medium text-ink">Metode <span class="text-red-500">*</span></label>
                                <select name="metode" class="form-select">
                                    <option value="transfer_manual">Transfer Manual</option>
                                    <option value="cash">Tunai</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label class="mb-[7px] block text-[13px] font-medium text-ink">Jumlah Bayar <span class="text-red-500">*</span></label>
                                <input type="text" name="jumlah_bayar" class="form-input rupiah-mask"
                                    value="{{ $tagihan->jumlah }}" placeholder="cth: 150.000">
                            </div>
                            <div class="form-field">
                                <label class="mb-[7px] block text-[13px] font-medium text-ink">Bukti Transfer</label>
                                <input type="file" name="bukti_transfer" accept="image/*,application/pdf"
                                    class="block w-full text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand">
                            </div>
                            <button type="submit"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-[10px] bg-green-600 px-4 text-sm font-semibold text-white hover:bg-green-700">
                                <i data-lucide="check"></i> Konfirmasi Pembayaran
                            </button>
                        </form>
                    @endcan
                </div>
            @endif

            <a href="{{ route('tagihan.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
                <i data-lucide="arrow-left"></i> Kembali ke daftar
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Buat QRIS
                $('#btn-qris').on('click', function () {
                    const btn = $(this).prop('disabled', true);
                    $.post('{{ route('pembayaran.createQris', $tagihan) }}')
                        .done((res) => {
                            const d = res.data || {};
                            Swal.fire({
                                icon: 'success',
                                title: 'QRIS dibuat',
                                html: `<div class="text-left text-sm">
                                    ${d.payment_url ? `<a href="${d.payment_url}" target="_blank" class="text-brand underline">Buka halaman pembayaran</a>` : ''}
                                    ${d.qr_string ? `<p class="mt-2 break-all text-xs text-slate-500">${d.qr_string}</p>` : ''}
                                </div>`,
                            });
                        })
                        .fail(Netvia.ajaxError)
                        .always(() => btn.prop('disabled', false));
                });

                // Manual form toggle
                $('#btn-manual-toggle').on('click', () => {
                    $('#form-manual').toggleClass('hidden');
                    Netvia.initRupiah($('#form-manual')[0]);
                });

                $('#form-manual').on('submit', function (e) {
                    e.preventDefault();
                    const form = this;
                    const data = new FormData(form);
                    data.set('jumlah_bayar', rupiah_cleanJS($('input[name=jumlah_bayar]', form).val()));
                    $.ajax({
                        url: form.action, method: 'POST', data, processData: false, contentType: false,
                    })
                        .done((res) => { Netvia.toast(res.message); setTimeout(() => location.reload(), 800); })
                        .fail(Netvia.ajaxError);
                });

                // Reminder
                $('#btn-reminder').on('click', function () {
                    const btn = $(this).prop('disabled', true);
                    $.post('{{ route('tagihan.kirimReminder', $tagihan) }}')
                        .done((res) => Netvia.toast(res.message))
                        .fail(Netvia.ajaxError)
                        .always(() => btn.prop('disabled', false));
                });

                // Void
                $('#btn-void').on('click', async function () {
                    const { value: alasan } = await Swal.fire({
                        title: 'Batalkan tagihan?',
                        input: 'text',
                        inputLabel: 'Alasan pembatalan',
                        inputPlaceholder: 'cth: salah terbit',
                        showCancelButton: true,
                        confirmButtonText: 'Batalkan',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#dc2626',
                        inputValidator: (v) => (!v ? 'Alasan wajib diisi.' : undefined),
                    });
                    if (!alasan) return;
                    $.post('{{ route('tagihan.void', $tagihan) }}', { alasan })
                        .done((res) => { Netvia.toast(res.message); setTimeout(() => location.reload(), 800); })
                        .fail(Netvia.ajaxError);
                });

                function rupiah_cleanJS(v) { return (v || '').toString().replace(/\D/g, ''); }
            });
        </script>
    @endpush
</x-layouts.app>
