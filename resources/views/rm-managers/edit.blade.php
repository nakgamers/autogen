<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ubah RM Manager') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="padding:1.5rem;">

                @if($errors->any())
                    <div style="background-color:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:0.375rem; padding:0.75rem 1rem; margin-bottom:1rem;">
                        <ul style="list-style:disc; padding-left:1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('rm-managers.update', $rmManager) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div style="margin-bottom:1rem;">
                        <label class="block text-sm font-medium text-gray-700">Nama RM</label>
                        <input type="text" name="name" value="{{ old('name', $rmManager->name) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="margin-top:0.25rem;">
                    </div>
                    <div style="margin-bottom:1.5rem;">
                        <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $rmManager->phone_number) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="margin-top:0.25rem;">
                    </div>
                    <div style="display:flex; gap:0.5rem;">
                        <a href="{{ route('rm-managers.index') }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#ffffff; color:#374151; border:1px solid #d1d5db; border-radius:0.375rem; font-size:0.875rem; font-weight:500; text-decoration:none;">Batal</a>
                        <button type="submit" style="padding:0.5rem 1rem; background-color:#4f46e5; color:#ffffff; border:none; border-radius:0.375rem; font-size:0.875rem; font-weight:500; cursor:pointer;">Simpan Perubahan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
