<x-layouts.app title="Paket">
    <x-slot:header>Paket Internet</x-slot:header>
    <x-slot:subheader>Kelola produk paket langganan yang dijual.</x-slot:subheader>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-ink">Daftar Paket</h2>
            @can('paket.create')
                <a href="{{ route('paket.create') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark">
                    <i data-lucide="plus"></i> Tambah Paket
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table id="paket-table" class="w-full">
                <thead>
                    <tr class="text-left">
                        <th>Nama</th>
                        <th>Kecepatan</th>
                        <th>Harga</th>
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
                const table = Netvia.dataTable('#paket-table', {
                    ajax: '{{ route('paket.data') }}',
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'nama', name: 'nama' },
                        {
                            data: 'kecepatan_mbps',
                            name: 'kecepatan_mbps',
                            render: (d) => (d ? d + ' Mbps' : '-'),
                        },
                        { data: 'harga', name: 'harga' },
                        { data: 'status', name: 'is_active', orderable: false, searchable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                    ],
                });

                table.on('click', '.btn-delete', async function () {
                    const id = $(this).data('id');
                    if (!(await Netvia.confirmDelete('Paket akan dihapus dan tidak bisa dikembalikan.'))) {
                        return;
                    }
                    $.ajax({ url: `/paket/${id}`, method: 'DELETE' })
                        .done((res) => {
                            Netvia.toast(res.message);
                            table.ajax.reload(null, false);
                        })
                        .fail(Netvia.ajaxError);
                });

                table.on('click', '.btn-toggle', function () {
                    const id = $(this).data('id');
                    $.ajax({ url: `/paket/${id}/toggle`, method: 'PATCH' })
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
