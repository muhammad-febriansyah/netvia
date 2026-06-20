<x-layouts.app title="Pemutusan">
    <x-slot:header>Pengajuan Pemutusan</x-slot:header>
    <x-slot:subheader>Tinjau permintaan berhenti berlangganan dari pelanggan.</x-slot:subheader>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
        <div class="mb-5 flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Status</label>
                <select id="filter-status" class="form-select !h-10 w-44">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="pemutusan-table" class="w-full">
                <thead>
                    <tr class="text-left">
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Alasan</th>
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
                const table = Netvia.dataTable('#pemutusan-table', {
                    ajax: {
                        url: '{{ route('pemutusan.data') }}',
                        data: (d) => { d.status = $('#filter-status').val(); },
                    },
                    order: [[0, 'desc']],
                    columns: [
                        { data: 'created_at', name: 'created_at' },
                        { data: 'pelanggan', name: 'pelanggan', orderable: false },
                        { data: 'alasan', name: 'alasan' },
                        { data: 'status_badge', name: 'status', orderable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                    ],
                });

                $('#filter-status').on('change', () => table.ajax.reload());

                table.on('click', '.btn-approve', async function () {
                    const id = $(this).data('id');
                    const res = await Swal.fire({
                        title: 'Setujui pemutusan?',
                        text: 'Pelanggan akan dinonaktifkan.',
                        icon: 'warning', showCancelButton: true,
                        confirmButtonText: 'Ya, setujui', cancelButtonText: 'Batal',
                    });
                    if (!res.isConfirmed) return;
                    $.post(`/pemutusan/${id}/approve`)
                        .done((r) => { Netvia.toast(r.message); table.ajax.reload(null, false); })
                        .fail(Netvia.ajaxError);
                });

                table.on('click', '.btn-reject', async function () {
                    const id = $(this).data('id');
                    const { value: catatan } = await Swal.fire({
                        title: 'Tolak pengajuan',
                        input: 'text', inputLabel: 'Alasan penolakan', inputPlaceholder: 'cth: data tidak lengkap',
                        showCancelButton: true, confirmButtonText: 'Tolak', cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                        inputValidator: (v) => (!v ? 'Alasan wajib diisi.' : undefined),
                    });
                    if (!catatan) return;
                    $.post(`/pemutusan/${id}/reject`, { catatan })
                        .done((r) => { Netvia.toast(r.message); table.ajax.reload(null, false); })
                        .fail(Netvia.ajaxError);
                });
            });
        </script>
    @endpush
</x-layouts.app>
