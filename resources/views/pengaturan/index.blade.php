<x-layouts.app title="Pengaturan">
    <x-slot:header>Pengaturan</x-slot:header>
    <x-slot:subheader>Konfigurasi profil perusahaan, billing, pembayaran &amp; notifikasi.</x-slot:subheader>

    @php
        $tabs = [
            'profil' => 'Profil Perusahaan',
            'billing' => 'Billing',
            'pembayaran' => 'Pembayaran',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'template' => 'Template',
        ];
        $canUpdate = auth()->user()?->can('pengaturan.update');
    @endphp

    <div class="flex flex-col gap-5 lg:flex-row" data-pengaturan>
        {{-- Tab nav --}}
        <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-line bg-white p-2 lg:w-56 lg:flex-none lg:flex-col">
            @foreach ($tabs as $key => $label)
                <button type="button" data-tab="{{ $key }}"
                    class="nv-tab whitespace-nowrap rounded-[10px] px-3.5 py-2.5 text-left text-sm font-medium text-slate-500 transition hover:bg-canvas hover:text-ink
                        @if ($loop->first) is-active @endif">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <div class="min-w-0 flex-1">
            {{-- PROFIL --}}
            <section data-panel="profil" class="rounded-2xl border border-line bg-white p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">Profil Perusahaan</h2>
                <form method="POST" action="{{ route('pengaturan.updateProfil') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')
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
                    <div>
                        <label class="mb-[7px] block text-[13px] font-medium text-ink">Logo Perusahaan</label>
                        @if (! empty($settings['logo']))
                            <img src="{{ Storage::url($settings['logo']) }}" alt="Logo" class="mb-2 h-12 rounded-lg border border-line bg-canvas p-1">
                        @endif
                        <input type="file" name="logo" accept="image/*" @disabled(! $canUpdate)
                            class="block w-full text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand">
                        @error('logo')<p class="mt-1.5 text-[12.5px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if ($canUpdate)
                        <x-pengaturan.save-button />
                    @endif
                </form>
            </section>

            {{-- BILLING --}}
            <section data-panel="billing" hidden class="rounded-2xl border border-line bg-white p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">Parameter Billing</h2>
                <form method="POST" action="{{ route('pengaturan.updateBilling') }}" class="space-y-4">
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
            <section data-panel="pembayaran" hidden class="rounded-2xl border border-line bg-white p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">Konfigurasi Pembayaran</h2>
                <form method="POST" action="{{ route('pengaturan.updatePembayaran') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <x-pengaturan.toggle name="qris_aktif" label="Aktifkan pembayaran QRIS (Pakasir)"
                        :checked="($settings['qris_aktif'] ?? '1') === '1'" :disabled="! $canUpdate" />
                    <div class="rounded-xl bg-canvas p-3.5 text-[12.5px] text-muted">
                        <span class="font-medium text-ink">Callback URL Pakasir:</span>
                        <code class="ml-1 break-all">{{ $callbackUrl }}</code>
                        <p class="mt-1">Kredensial <code>PAKASIR_API_KEY</code> &amp; <code>PAKASIR_PROJECT</code> diambil dari <code>.env</code>.</p>
                    </div>
                    <p class="text-[13px] font-medium text-ink">Info rekening manual (instruksi transfer)</p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-form.input name="bank_nama" label="Bank" :value="$settings['bank_nama'] ?? ''"
                            placeholder="cth: BCA" :disabled="! $canUpdate" />
                        <x-form.input name="bank_no_rekening" label="No. Rekening" :value="$settings['bank_no_rekening'] ?? ''"
                            placeholder="cth: 1234567890" :disabled="! $canUpdate" />
                        <x-form.input name="bank_atas_nama" label="Atas Nama" :value="$settings['bank_atas_nama'] ?? ''"
                            placeholder="cth: PT Netvia" :disabled="! $canUpdate" />
                    </div>
                    @if ($canUpdate)
                        <x-pengaturan.save-button />
                    @endif
                </form>
            </section>

            {{-- WHATSAPP --}}
            <section data-panel="whatsapp" hidden class="rounded-2xl border border-line bg-white p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">Konfigurasi WhatsApp</h2>
                <form method="POST" action="{{ route('pengaturan.updateWhatsapp') }}" class="space-y-4">
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
            <section data-panel="email" hidden class="rounded-2xl border border-line bg-white p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">Konfigurasi Email</h2>
                <form method="POST" action="{{ route('pengaturan.updateEmail') }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="rounded-xl bg-canvas p-3.5 text-[12.5px] text-muted">
                        Driver/SMTP &amp; kredensial provider diatur di <code>.env</code> (<code>MAIL_*</code>).
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
            <section data-panel="template" hidden class="rounded-2xl border border-line bg-white p-6">
                <h2 class="mb-2 text-base font-semibold text-ink">Template Pesan</h2>
                <p class="text-sm text-muted">
                    Kelola template reminder &amp; struk per jenis &amp; channel di modul Notifikasi
                    (<code class="rounded bg-canvas px-1.5 py-0.5 text-xs">09-notifikasi.md</code>).
                </p>
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        $(function () {
            const root = $('[data-pengaturan]');
            root.find('.nv-tab').on('click', function () {
                const key = $(this).data('tab');
                root.find('.nv-tab').removeClass('is-active');
                $(this).addClass('is-active');
                root.find('[data-panel]').attr('hidden', true);
                root.find(`[data-panel="${key}"]`).removeAttr('hidden');
            });
        });
    </script>
    @endpush
</x-layouts.app>
