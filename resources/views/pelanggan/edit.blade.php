<x-layouts.app title="Edit Pelanggan">
    <x-slot:header>Edit Pelanggan</x-slot:header>
    <x-slot:subheader>Perbarui data {{ $pelanggan->nama }}.</x-slot:subheader>

    <div class="max-w-3xl rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
        <form method="POST" action="{{ route('pelanggan.update', $pelanggan) }}">
            @csrf
            @method('PUT')
            @include('pelanggan._form', ['pelanggan' => $pelanggan])
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
