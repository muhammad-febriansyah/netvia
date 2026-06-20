<x-layouts.app title="Pengaturan">
    <x-slot:header>Pengaturan</x-slot:header>
    <x-slot:subheader>Konfigurasi profil perusahaan, billing, pembayaran &amp; notifikasi.</x-slot:subheader>

    @php
        $tabs = [
            'profil' => ['label' => 'Profil Perusahaan', 'icon' => 'building-2', 'desc' => 'Identitas perusahaan yang tampil di sidebar, invoice & notifikasi.'],
            'billing' => ['label' => 'Billing', 'icon' => 'receipt-text', 'desc' => 'Aturan penerbitan tagihan & jadwal reminder jatuh tempo.'],
            'pembayaran' => ['label' => 'Pembayaran', 'icon' => 'credit-card', 'desc' => 'Metode pembayaran QRIS otomatis & info rekening transfer manual.'],
            'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'message-circle', 'desc' => 'Gateway pengiriman pesan WhatsApp ke pelanggan.'],
            'email' => ['label' => 'Email', 'icon' => 'mail', 'desc' => 'Identitas pengirim email keluar.'],
            'template' => ['label' => 'Template', 'icon' => 'file-text', 'desc' => 'Template pesan reminder & struk.'],
        ];
        $canUpdate = auth()->user()?->can('pengaturan.update');
    @endphp

    <div class="flex flex-col gap-5 lg:flex-row" data-pengaturan>
        {{-- Tab nav --}}
        <nav class="flex gap-1.5 overflow-x-auto rounded-2xl border border-line bg-white p-2 lg:w-64 lg:flex-none lg:flex-col">
            @foreach ($tabs as $key => $tab)
                <button type="button" data-tab="{{ $key }}"
                    class="nv-tab group flex items-center gap-3 whitespace-nowrap rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-500 transition hover:bg-canvas hover:text-ink
                        @if ($loop->first) is-active @endif">
                    <span class="flex size-8 flex-none items-center justify-center rounded-lg bg-canvas text-slate-400 transition group-[.is-active]:bg-brand group-[.is-active]:text-white">
                        <i data-lucide="{{ $tab['icon'] }}" class="size-[17px]"></i>
                    </span>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        <div class="min-w-0 flex-1">
            {{-- PROFIL --}}
            <section data-panel="profil" class="rounded-2xl border border-line bg-white">
                <x-pengaturan.section-head icon="building-2" title="Profil Perusahaan"
                    desc="Identitas ini dipakai di sidebar, invoice & pesan otomatis." />
                <form method="POST" action="{{ route('pengaturan.updateProfil') }}" enctype="multipart/form-data" class="space-y-5 p-6">
                    @csrf @method('PUT')

                    {{-- Logo uploader --}}
                    <div>
                        <label class="mb-[7px] block text-[13px] font-medium text-ink">Logo Perusahaan</label>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center" data-logo-uploader>
                            <div class="flex h-24 w-24 flex-none items-center justify-center overflow-hidden rounded-2xl border border-line bg-canvas p-2">
                                @if (! empty($settings['logo']))
                                    <img data-logo-preview src="{{ Storage::url($settings['logo']) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                @else
                                    <img data-logo-preview src="" alt="" class="hidden max-h-full max-w-full object-contain">
                                    <i data-logo-placeholder data-lucide="image" class="size-8 text-slate-300"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="@if (! $canUpdate) pointer-events-none opacity-60 @endif inline-flex cursor-pointer items-center gap-2 rounded-[10px] border border-line bg-white px-4 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas">
                                    <i data-lucide="upload" class="size-4"></i>
                                    Pilih Logo
                                    <input type="file" name="logo" accept="image/*" class="sr-only" data-logo-input @disabled(! $canUpdate)>
                                </label>
                                <p data-logo-name class="mt-2 truncate text-[12.5px] text-muted">PNG, JPG atau WEBP. Maks 1 MB. Disarankan latar transparan.</p>
                                @error('logo')<p class="mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <x-form.input name="nama_perusahaan" label="Nama Perusahaan" required
                        :value="$settings['nama_perusahaan'] ?? ''" placeholder="cth: Netvia Net" :disabled="! $canUpdate" />
                    <x-form.textarea name="alamat" label="Alamat" :value="$settings['alamat'] ?? ''"
                        placeholder="alamat kantor / NOC" :disabled="! $canUpdate" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.input name="no_telp" label="No. Telepon" :value="$settings['no_telp'] ?? ''"
                            placeholder="cth: 021-1234567" :disabled="! $canUpdate" />
                        <x-form.input name="no_wa_cs" label="No. WA CS" :value="$settings['no_wa_cs'] ?? ''"
                            placeholder="cth: 081234567890" :disabled="! $canUpdate" />
                    </div>
                    <x-form.input name="email_cs" label="Email CS" type="email" :value="$settings['email_cs'] ?? ''"
                        placeholder="cth: cs@netvia.id" :disabled="! $canUpdate" />
                    <x-form.textarea name="footer_invoice" label="Footer Invoice" :value="$settings['footer_invoice'] ?? ''"
                        placeholder="teks di bagian bawah invoice/struk" :disabled="! $canUpdate" />

                    @if ($canUpdate)
                        <x-pengaturan.save-button />
                    @endif
                </form>
            </section>

            {{-- BILLING --}}
            <section data-panel="billing" hidden class="rounded-2xl border border-line bg-white">
                <x-pengaturan.section-head icon="receipt-text" title="Parameter Billing"
                    desc="Kendalikan kapan tagihan terbit & kapan reminder dikirim." />
                <form method="POST" action="{{ route('pengaturan.updateBilling') }}" class="space-y-4 p-6">
                    @csrf @method('PUT')
                    <x-form.input name="generate_hari_sebelum_jatuh_tempo" label="Generate Tagihan (hari sebelum jatuh tempo)"
                        type="number" required :value="$settings['generate_hari_sebelum_jatuh_tempo'] ?? '7'"
                        placeholder="cth: 7" help="Tagihan terbit N hari sebelum tanggal jatuh tempo pelanggan (0–28)." :disabled="! $canUpdate" />
                    <x-form.input name="reminder_overdue_hari" label="Hari Reminder Overdue"
                        :value="$settings['reminder_overdue_hari'] ?? '1,3,7'"
                        placeholder="cth: 1,3,7" help="Pada H+berapa reminder telat dikirim. Pisahkan dengan koma." :disabled="! $canUpdate" />
                    <x-pengaturan.toggle name="kirim_invoice_baru" label="Kirim notifikasi saat tagihan baru terbit"
                        :checked="($settings['kirim_invoice_baru'] ?? '1') === '1'" :disabled="! $canUpdate" />
                    @if ($canUpdate)
                        <x-pengaturan.save-button />
                    @endif
                </form>
            </section>

            {{-- PEMBAYARAN --}}
            <section data-panel="pembayaran" hidden class="rounded-2xl border border-line bg-white">
                <x-pengaturan.section-head icon="credit-card" title="Konfigurasi Pembayaran"
                    desc="Aktifkan QRIS otomatis & atur info rekening untuk transfer manual." />
                <form method="POST" action="{{ route('pengaturan.updatePembayaran') }}" class="space-y-4 p-6">
                    @csrf @method('PUT')
                    <x-pengaturan.toggle name="qris_aktif" label="Aktifkan pembayaran QRIS (Pakasir)"
                        :checked="($settings['qris_aktif'] ?? '1') === '1'" :disabled="! $canUpdate" />
                    <div class="flex gap-2.5 rounded-xl border border-line bg-canvas p-3.5 text-[12.5px] text-muted">
                        <i data-lucide="info" class="mt-0.5 size-4 flex-none text-brand"></i>
                        <div>
                            <span class="font-medium text-ink">Callback URL Pakasir:</span>
                            <code class="ml-1 break-all">{{ $callbackUrl }}</code>
                            <p class="mt-1">Kredensial <code>PAKASIR_API_KEY</code> &amp; <code>PAKASIR_PROJECT</code> diambil dari <code>.env</code>.</p>
                        </div>
                    </div>
                    <div class="border-t border-line pt-4">
                        <p class="text-[13px] font-medium text-ink">Info rekening manual (instruksi transfer)</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-3">
                            <x-form.input name="bank_nama" label="Bank" :value="$settings['bank_nama'] ?? ''"
                                placeholder="cth: BCA" :disabled="! $canUpdate" />
                            <x-form.input name="bank_no_rekening" label="No. Rekening" :value="$settings['bank_no_rekening'] ?? ''"
                                placeholder="cth: 1234567890" :disabled="! $canUpdate" />
                            <x-form.input name="bank_atas_nama" label="Atas Nama" :value="$settings['bank_atas_nama'] ?? ''"
                                placeholder="cth: PT Netvia" :disabled="! $canUpdate" />
                        </div>
                    </div>
                    @if ($canUpdate)
                        <x-pengaturan.save-button />
                    @endif
                </form>
            </section>

            {{-- WHATSAPP --}}
            <section data-panel="whatsapp" hidden class="rounded-2xl border border-line bg-white">
                <x-pengaturan.section-head icon="message-circle" title="Konfigurasi WhatsApp"
                    desc="Pilih gateway & nomor pengirim untuk pesan otomatis." />
                <form method="POST" action="{{ route('pengaturan.updateWhatsapp') }}" class="space-y-4 p-6">
                    @csrf @method('PUT')
                    <div class="form-field">
                        <label for="wa_driver" class="mb-[7px] block text-[13px] font-medium text-ink">Driver <span class="text-red-500">*</span></label>
                        <select id="wa_driver" name="wa_driver" class="form-select" @disabled(! $canUpdate)>
                            @foreach (['cloud_api' => 'Meta Cloud API', 'gateway' => 'Gateway (Fonnte/Wablas)', 'chatcepat' => 'ChatCepat', 'log' => 'Log (dev/testing)'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(($settings['wa_driver'] ?? 'cloud_api') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('wa_driver')<p class="mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <x-form.input name="wa_nomor_pengirim" label="Nomor Pengirim" :value="$settings['wa_nomor_pengirim'] ?? ''"
                        placeholder="cth: 081290001234" help="Token/endpoint driver disimpan di .env." :disabled="! $canUpdate" />
                    @if ($canUpdate)
                        <div class="flex flex-wrap items-center gap-3">
                            <x-pengaturan.save-button />
                            <x-pengaturan.test-button target="wa" :route="route('pengaturan.tesWhatsapp')"
                                field="no_wa" placeholder="No. WA tujuan tes" />
                        </div>
                    @endif
                </form>
            </section>

            {{-- EMAIL --}}
            <section data-panel="email" hidden class="rounded-2xl border border-line bg-white">
                <x-pengaturan.section-head icon="mail" title="Konfigurasi Email"
                    desc="Identitas pengirim email keluar (SMTP diatur di .env)." />
                <form method="POST" action="{{ route('pengaturan.updateEmail') }}" class="space-y-4 p-6">
                    @csrf @method('PUT')
                    <div class="flex gap-2.5 rounded-xl border border-line bg-canvas p-3.5 text-[12.5px] text-muted">
                        <i data-lucide="info" class="mt-0.5 size-4 flex-none text-brand"></i>
                        <div>Driver/SMTP &amp; kredensial provider diatur di <code>.env</code> (<code>MAIL_*</code>).</div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.input name="email_from_name" label="From Name" :value="$settings['email_from_name'] ?? ''"
                            placeholder="cth: Netvia Net" :disabled="! $canUpdate" />
                        <x-form.input name="email_from_address" label="From Address" type="email" :value="$settings['email_from_address'] ?? ''"
                            placeholder="cth: no-reply@netvia.id" :disabled="! $canUpdate" />
                    </div>
                    @if ($canUpdate)
                        <div class="flex flex-wrap items-center gap-3">
                            <x-pengaturan.save-button />
                            <x-pengaturan.test-button target="email" :route="route('pengaturan.tesEmail')"
                                field="email" placeholder="Email tujuan tes" />
                        </div>
                    @endif
                </form>
            </section>

            {{-- TEMPLATE --}}
            <section data-panel="template" hidden class="rounded-2xl border border-line bg-white">
                <x-pengaturan.section-head icon="file-text" title="Template Pesan"
                    desc="Kelola isi pesan reminder & struk pelanggan." />
                <div class="p-6">
                    <div class="flex items-start gap-3 rounded-xl border border-line bg-canvas p-4">
                        <i data-lucide="arrow-right-circle" class="mt-0.5 size-5 flex-none text-brand"></i>
                        <p class="text-sm text-muted">
                            Kelola template reminder &amp; struk per jenis &amp; channel di modul
                            <a href="{{ route('notifikasi.index') }}" class="font-medium text-brand hover:underline">Notifikasi</a>.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        $(function () {
            const root = $('[data-pengaturan]');

            // Tab switching.
            root.find('.nv-tab').on('click', function () {
                const key = $(this).data('tab');
                root.find('.nv-tab').removeClass('is-active');
                $(this).addClass('is-active');
                root.find('[data-panel]').attr('hidden', true);
                root.find(`[data-panel="${key}"]`).removeAttr('hidden');
            });

            // Live logo preview.
            root.find('[data-logo-input]').on('change', function () {
                const file = this.files && this.files[0];
                const box = $(this).closest('[data-logo-uploader]');
                if (!file) { return; }
                box.find('[data-logo-name]').text(file.name);
                const reader = new FileReader();
                reader.onload = (e) => {
                    box.find('[data-logo-placeholder]').addClass('hidden');
                    box.find('[data-logo-preview]').attr('src', e.target.result).removeClass('hidden');
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
    @endpush
</x-layouts.app>
