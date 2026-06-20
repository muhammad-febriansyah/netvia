<x-layouts.app title="Rekap Pelanggan">
    <x-slot:header>Rekap Pelanggan</x-slot:header>
    <x-slot:subheader>Daftar pelanggan, paket, status &amp; saldo tunggakan.</x-slot:subheader>

    @include('laporan._assets')

    <div class="rounded-2xl border border-line bg-white p-5 shadow-[0_1px_2px_rgba(16,24,64,.035)]">
        <div class="mb-5 flex flex-wrap items-end gap-3">
            @can('laporan.export')
                <a href="{{ route('laporan.pelangganExport') }}" class="inline-flex h-[42px] items-center gap-1.5 rounded-[10px] bg-green-600 px-4 text-sm font-semibold text-white hover:bg-green-700">
                    <i data-lucide="file-spreadsheet"></i> Excel
                </a>
                <a href="{{ route('laporan.pelangganExport', ['type' => 'pdf']) }}" class="inline-flex h-[42px] items-center gap-1.5 rounded-[10px] bg-red-600 px-4 text-sm font-semibold text-white hover:bg-red-700">
                    <i data-lucide="file-text"></i> PDF
                </a>
            @endcan
        </div>

        <table id="tbl" class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted">
                    <th>Kode</th><th>Nama</th><th>Paket</th><th>Status</th><th class="text-right">Tunggakan</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
        <script>
            $(function () {
                $('#tbl').DataTable({
                    processing: true, serverSide: true,
                    ajax: @json(route('laporan.pelangganData')),
                    columns: [
                        { data: 'kode_pelanggan' },
                        { data: 'nama' },
                        { data: 'paket', orderable: false },
                        { data: 'status_label', orderable: false },
                        { data: 'tunggakan', className: 'text-right', orderable: false },
                    ],
                });
                window.lucide?.createIcons();
            });
        </script>
    @endpush
</x-layouts.app>
