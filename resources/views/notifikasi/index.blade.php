<x-layouts.app title="Notifikasi">
    <x-slot:header>Log Notifikasi</x-slot:header>
    <x-slot:subheader>Riwayat pengiriman reminder &amp; struk via WhatsApp dan Email.</x-slot:subheader>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Channel</label>
                    <select id="filter-channel" class="form-select !h-10 w-40">
                        <option value="">Semua</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->value }}">{{ ucfirst($channel->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Jenis</label>
                    <select id="filter-jenis" class="form-select !h-10 w-52">
                        <option value="">Semua</option>
                        @foreach ($jenisList as $jenis)
                            <option value="{{ $jenis->value }}">{{ $jenis->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[12.5px] font-medium text-muted">Status</label>
                    <select id="filter-status" class="form-select !h-10 w-40">
                        <option value="">Semua</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @can('notifikasi.template')
                <a href="{{ route('notifikasi.template') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-[10px] border border-line px-4 text-sm font-medium text-ink hover:bg-canvas">
                    <i data-lucide="file-text"></i> Kelola Template
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table id="notifikasi-table" class="w-full">
                <thead>
                    <tr class="text-left">
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Channel</th>
                        <th>Jenis</th>
                        <th>Tujuan</th>
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
                const table = Netvia.dataTable('#notifikasi-table', {
                    ajax: {
                        url: '{{ route('notifikasi.data') }}',
                        data: (d) => {
                            d.channel = $('#filter-channel').val();
                            d.jenis = $('#filter-jenis').val();
                            d.status = $('#filter-status').val();
                        },
                    },
                    order: [[0, 'desc']],
                    columns: [
                        { data: 'created_at', name: 'created_at' },
                        { data: 'pelanggan', name: 'pelanggan', orderable: false },
                        { data: 'channel_label', name: 'channel', orderable: false },
                        { data: 'jenis_label', name: 'jenis', orderable: false },
                        { data: 'recipient', name: 'recipient' },
                        { data: 'status_badge', name: 'status', orderable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                    ],
                });

                $('#filter-channel, #filter-jenis, #filter-status').on('change', () => table.ajax.reload());

                table.on('click', '.btn-payload', function () {
                    const payload = $(this).data('payload');
                    const error = $(this).data('error');
                    Swal.fire({
                        title: 'Isi Notifikasi',
                        html: `<pre class="whitespace-pre-wrap text-left text-sm text-slate-700">${payload || '(kosong)'}</pre>`
                            + (error ? `<p class="mt-3 text-left text-sm text-red-600">${error}</p>` : ''),
                    });
                });

                table.on('click', '.btn-resend', function () {
                    const id = $(this).data('id');
                    $.post(`/notifikasi/${id}/resend`)
                        .done((res) => { Netvia.toast(res.message); table.ajax.reload(null, false); })
                        .fail(Netvia.ajaxError);
                });
            });
        </script>
    @endpush
</x-layouts.app>
