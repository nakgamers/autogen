<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="padding:1.5rem;">

                @if(session('success'))
                    <div style="background-color:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:0.375rem; padding:0.75rem 1rem; margin-bottom:1rem;">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div style="background-color:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:0.375rem; padding:0.75rem 1rem; margin-bottom:1rem;">{{ $errors->first() }}</div>
                @endif

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <p class="text-sm text-gray-500">Total {{ $users->count() }} pengguna</p>
                    <a href="{{ route('users.create') }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#4f46e5; color:#ffffff; border-radius:0.375rem; font-size:0.875rem; font-weight:500; text-decoration:none;">+ Buat Akun</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Terdaftar</th>
                                <th class="px-4 py-3" style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $u)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $u->name }}</td>
                                    <td class="px-4 py-3">{{ $u->email }}</td>
                                    <td class="px-4 py-3">{{ $u->roleLabel() }}</td>
                                    <td class="px-4 py-3">{{ $u->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3" style="text-align:center; white-space:nowrap;">
                                        <a href="{{ route('users.edit', $u) }}" style="color:#4f46e5; font-weight:500; text-decoration:none;">Ubah</a>
                                        @if($u->id !== auth()->id())
                                            <span class="text-gray-300">|</span>
                                            <form action="{{ route('users.destroy', $u) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus akun {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="color:#dc2626; font-weight:500; background:none; border:none; cursor:pointer;">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
