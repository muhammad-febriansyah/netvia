<x-layouts.app title="Pelanggan">
    <x-slot:header>Pelanggan</x-slot:header>
    <x-slot:subheader>Kelola data pelanggan dan langganan paketnya.</x-slot:subheader>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="w-full sm:w-52">
                    <label class="mb-[6px] block text-[12px] font-medium text-muted">Filter Paket</label>
                    <select id="filter-paket" class="select2" data-placeholder="Semua paket" data-allow-clear="true">
                        <option value=""></option>
                        @foreach ($pakets as $paket)
                            <option value="{{ $paket->id }}">{{ $paket->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <label class="mb-[6px] block text-[12px] font-medium text-muted">Filter Status</label>
                    <select id="filter-status" class="select2" data-placeholder="Semua status" data-allow-clear="true">
                        <option value=""></option>
                        @foreach (\App\Enums\PelangganStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @can('pelanggan.create')
                <a href="{{ route('pelanggan.create') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark">
                    <i data-lucide="plus"></i> Tambah Pelanggan
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table id="pelanggan-table" class="w-full">
                <thead>
                    <tr class="text-left">
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>No. WA</th>
                        <th>Paket</th>
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
                const table = Netvia.dataTable('#pelanggan-table', {
                    ajax: {
                        url: '{{ route('pelanggan.data') }}',
                        data: function (d) {
                            d.paket_id = $('#filter-paket').val();
                            d.status = $('#filter-status').val();
                        },
                    },
                    order: [[1, 'asc']],
                    columns: [
                        { data: 'kode_pelanggan', name: 'kode_pelanggan' },
                        { data: 'nama', name: 'nama' },
                        { data: 'no_wa', name: 'no_wa' },
                        { data: 'paket_nama', name: 'paket_nama', orderable: false, searchable: false },
                        { data: 'tgl_jatuh_tempo', name: 'tgl_jatuh_tempo', render: (d) => 'Tgl ' + d },
                        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                    ],
                });

                $('#filter-paket, #filter-status').on('change', () => table.ajax.reload());

                table.on('click', '.btn-delete', async function () {
                    const id = $(this).data('id');
                    if (!(await Netvia.confirmDelete('Pelanggan akan dihapus dari daftar.'))) {
                        return;
                    }
                    $.ajax({ url: `/pelanggan/${id}`, method: 'DELETE' })
                        .done((res) => {
                            Netvia.toast(res.message);
                            table.ajax.reload(null, false);
                        })
                        .fail(Netvia.ajaxError);
                });
            });
        </script>
    @endpush
</x-layouts.app>
