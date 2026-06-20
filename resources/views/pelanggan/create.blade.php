<x-layouts.app title="Tambah Pelanggan">
    <x-slot:header>Tambah Pelanggan</x-slot:header>
    <x-slot:subheader>Daftarkan pelanggan baru beserta paket langganannya.</x-slot:subheader>

    <div class="max-w-3xl rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
        <form method="POST" action="{{ route('pelanggan.store') }}">
            @csrf
            @include('pelanggan._form')
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('form').on('submit', function () {
                    $(this).find('.btn-submit').prop('disabled', true);
                });
            });
        </script>
    @endpush
</x-layouts.app>
