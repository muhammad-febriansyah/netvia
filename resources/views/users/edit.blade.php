<x-layouts.app title="Edit Pengguna">
    <x-slot:header>Edit Pengguna</x-slot:header>
    <x-slot:subheader>{{ $user->name }}</x-slot:subheader>

    <div class="max-w-2xl rounded-2xl bg-white p-7 shadow-sm ring-1 ring-line">
        <form method="POST" action="{{ route('user.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')
            @include('users._form')
        </form>
    </div>
</x-layouts.app>
