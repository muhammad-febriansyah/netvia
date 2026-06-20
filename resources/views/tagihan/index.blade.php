<x-layouts.app title="Tagihan">
    <x-slot:header>Tagihan</x-slot:header>
    <x-slot:subheader>Daftar tagihan pelanggan per periode.</x-slot:subheader>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
        <div class="mb-5 flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Periode</label>
                <input type="month" id="filter-periode" class="form-input !h-10 w-44">
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Status</label>
                <select id="filter-status" class="form-select !h-10 w-44">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" id="filter-reset"
                class="inline-flex h-10 items-center gap-1.5 rounded-[10px] border border-line px-3.5 text-sm font-medium text-muted hover:bg-canvas">
                <i data-lucide="rotate-ccw"></i> Reset
            </button>
        </div>

        <div class="overflow-x-auto">
            <table id="tagihan-table" class="w-full">
                <thead>
                    <tr class="text-left">
                        <th>No. Tagihan</th>
                        <th>Pelanggan</th>
                        <th>Periode</th>
                        <th>Jumlah</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const table = Netvia.dataTable('#tagihan-table', {
                    ajax: {
                        url: '{{ route('tagihan.data') }}',
                        data: (d) => {
                            d.periode = $('#filter-periode').val();
                            d.status = $('#filter-status').val();
                        },
                    },
                    order: [[2, 'desc']],
                    columns: [
                        { data: 'nomor_tagihan', name: 'nomor_tagihan' },
                        { data: 'pelanggan', name: 'pelanggan' },
                        { data: 'periode', name: 'periode' },
                        { data: 'jumlah', name: 'jumlah' },
                        { data: 'tanggal_jatuh_tempo', name: 'tanggal_jatuh_tempo' },
                        { data: 'status_badge', name: 'status', orderable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                    ],
                });

                $('#filter-periode, #filter-status').on('change', () => table.ajax.reload());
                $('#filter-reset').on('click', () => {
                    $('#filter-periode').val('');
                    $('#filter-status').val('');
                    table.ajax.reload();
                });
            });
        </script>
    @endpush
</x-layouts.app>
