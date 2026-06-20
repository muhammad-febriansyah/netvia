<x-layouts.app title="Edit Paket">
    <x-slot:header>Edit Paket</x-slot:header>
    <x-slot:subheader>Perbarui data paket {{ $paket->nama }}.</x-slot:subheader>

    <div class="max-w-2xl rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
        <form method="POST" action="{{ route('paket.update', $paket) }}">
            @csrf
            @method('PUT')
            @include('paket._form', ['paket' => $paket])
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
