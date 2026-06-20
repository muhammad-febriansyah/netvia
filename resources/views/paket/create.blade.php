<x-layouts.app title="Tambah Paket">
    <x-slot:header>Tambah Paket</x-slot:header>
    <x-slot:subheader>Buat produk paket internet baru.</x-slot:subheader>

    <div class="max-w-2xl rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
        <form method="POST" action="{{ route('paket.store') }}">
            @csrf
            @include('paket._form')
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
