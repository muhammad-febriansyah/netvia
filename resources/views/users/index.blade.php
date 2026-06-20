<x-layouts.app title="Pengguna">
    <x-slot:header>Manajemen Pengguna</x-slot:header>
    <x-slot:subheader>Kelola akun staf, peran, dan status akses.</x-slot:subheader>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-line">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-ink">Daftar Pengguna</h2>
            @can('user.create')
                <a href="{{ route('user.create') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-brand px-4 text-sm font-semibold text-white hover:bg-brand-dark">
                    <i data-lucide="user-plus"></i> Tambah Pengguna
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table id="user-table" class="w-full">
                <thead>
                    <tr class="text-left">
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
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
                const table = Netvia.dataTable('#user-table', {
                    ajax: '{{ route('user.data') }}',
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email' },
                        { data: 'role', name: 'role', orderable: false, searchable: false },
                        { data: 'status', name: 'is_active', orderable: false, searchable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right' },
                    ],
                });

                table.on('click', '.btn-delete', async function () {
                    const id = $(this).data('id');
                    if (!(await Netvia.confirmDelete('Pengguna akan dihapus.'))) return;
                    $.ajax({ url: `/users/${id}`, method: 'DELETE' })
                        .done((res) => { Netvia.toast(res.message); table.ajax.reload(null, false); })
                        .fail(Netvia.ajaxError);
                });

                table.on('click', '.btn-toggle', function () {
                    const id = $(this).data('id');
                    $.ajax({ url: `/users/${id}/toggle`, method: 'PATCH' })
                        .done((res) => { Netvia.toast(res.message); table.ajax.reload(null, false); })
                        .fail(Netvia.ajaxError);
                });

                table.on('click', '.btn-reset', async function () {
                    const id = $(this).data('id');
                    const { value: password } = await Swal.fire({
                        title: 'Reset Kata Sandi',
                        input: 'password',
                        inputLabel: 'Kata sandi baru',
                        inputPlaceholder: 'minimal 8 karakter',
                        showCancelButton: true,
                        confirmButtonText: 'Reset',
                        cancelButtonText: 'Batal',
                        inputValidator: (v) => (!v || v.length < 8 ? 'Minimal 8 karakter.' : undefined),
                    });
                    if (!password) return;
                    $.post(`/users/${id}/reset-password`, { password })
                        .done((res) => Netvia.toast(res.message))
                        .fail(Netvia.ajaxError);
                });
            });
        </script>
    @endpush
</x-layouts.app>
