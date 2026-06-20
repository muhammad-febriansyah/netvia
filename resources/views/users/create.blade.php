<x-layouts.app title="Tambah Pengguna">
    <x-slot:header>Tambah Pengguna</x-slot:header>
    <x-slot:subheader>Buat akun staf baru dan tetapkan perannya.</x-slot:subheader>

    <div class="max-w-2xl rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
        <form method="POST" action="{{ route('user.store') }}" class="space-y-4">
            @csrf
            @include('users._form')
        </form>
    </div>
</x-layouts.app>
