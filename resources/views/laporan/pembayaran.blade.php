<x-layouts.app title="Laporan Pembayaran">
    <x-slot:header>Riwayat Pembayaran</x-slot:header>
    <x-slot:subheader>Seluruh transaksi pembayaran per rentang tanggal.</x-slot:subheader>

    @include('laporan._assets')

    <div class="rounded-2xl border border-line bg-white p-5 shadow-[0_1px_2px_rgba(16,24,64,.035)]">
        <form id="filter" class="mb-5 flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-[13px] font-medium text-ink">Dari <span class="text-red-500">*</span></label>
                <input type="date" name="from" value="{{ $from }}" class="h-[42px] rounded-[10px] border border-line px-3 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-[13px] font-medium text-ink">Sampai <span class="text-red-500">*</span></label>
                <input type="date" name="to" value="{{ $to }}" class="h-[42px] rounded-[10px] border border-line px-3 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-[13px] font-medium text-ink">Status</label>
                <select name="status" class="h-[42px] rounded-[10px] border border-line px-3 text-sm">
                    <option value="">Semua</option>
                    <option value="pending">Menunggu</option>
                    <option value="success">Berhasil</option>
                    <option value="failed">Gagal</option>
                    <option value="expired">Kedaluwarsa</option>
                </select>
            </div>
            <button type="submit" class="h-[42px] rounded-[10px] bg-brand px-4 text-sm font-semibold text-white hover:bg-brand-dark">Terapkan</button>
            @can('laporan.export')
                <a id="export-excel" href="#" class="inline-flex h-[42px] items-center gap-1.5 rounded-[10px] bg-green-600 px-4 text-sm font-semibold text-white hover:bg-green-700">
                    <i data-lucide="file-spreadsheet"></i> Excel
                </a>
                <a id="export-pdf" href="#" class="inline-flex h-[42px] items-center gap-1.5 rounded-[10px] bg-red-600 px-4 text-sm font-semibold text-white hover:bg-red-700">
                    <i data-lucide="file-text"></i> PDF
                </a>
            @endcan
        </form>

        <table id="tbl" class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted">
                    <th>Tanggal</th><th>No Tagihan</th><th>Pelanggan</th><th>Metode</th><th>Status</th><th class="text-right">Jumlah</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const params = () => ({ from: $('[name=from]').val(), to: $('[name=to]').val(), status: $('[name=status]').val() });
                const table = $('#tbl').DataTable({
                    processing: true, serverSide: true,
                    ajax: { url: @json(route('laporan.pembayaranData')), data: d => Object.assign(d, params()) },
                    columns: [
                        { data: 'tanggal', orderable: false },
                        { data: 'nomor_tagihan', orderable: false },
                        { data: 'pelanggan', orderable: false },
                        { data: 'metode', orderable: false },
                        { data: 'status_label', orderable: false },
                        { data: 'jumlah_bayar', className: 'text-right', orderable: false },
                    ],
                });
                const sync = () => {
                    const qs = new URLSearchParams(params());
                    $('#export-excel').attr('href', @json(route('laporan.pembayaranExport')) + '?' + qs);
                    $('#export-pdf').attr('href', @json(route('laporan.pembayaranExport')) + '?type=pdf&' + qs);
                };
                $('#filter').on('submit', e => { e.preventDefault(); table.ajax.reload(); sync(); });
                sync();
                window.lucide?.createIcons();
            });
        </script>
    @endpush
</x-layouts.app>
