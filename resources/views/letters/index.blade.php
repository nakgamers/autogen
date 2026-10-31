<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Surat (30 Hari Terakhir)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <!-- Form Pencarian & Tombol Buat Surat -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <form action="{{ route('letters.index') }}" method="GET" class="w-full md:w-1/2 flex gap-2">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari berdasarkan nomor, perihal, atau penerima..." 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('letters.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center">
                                Reset
                            </a>
                        @endif
                    </form>

                    @if(auth()->user()->isTeamLeader())
                    <a href="{{ route('letters.create') }}" class="w-full md:w-auto text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium">
                        + Buat Surat Baru
                    </a>
                    @endif
                </div>

                @if(auth()->user()->isDepartmentHead())
                <!-- Filter Department Head -->
                <form action="{{ route('letters.index') }}" method="GET" style="border:1px solid #e5e7eb; border-radius:0.5rem; padding:1rem; margin-bottom:1.5rem; background-color:#f8fafc;">
                    <p class="text-sm font-semibold text-gray-700" style="margin-bottom:0.75rem;">Filter Riwayat — Semua Team Leader</p>
                    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:0.75rem;">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Team Leader</label>
                            <select name="team_leader" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="margin-top:0.25rem;">
                                <option value="">Semua Team Leader</option>
                                @foreach($teamLeaders as $tl)
                                    <option value="{{ $tl->id }}" @selected(request('team_leader') == $tl->id)>{{ $tl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="margin-top:0.25rem;">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="margin-top:0.25rem;">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">Jenis Surat</label>
                            <select name="letter_type_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style="margin-top:0.25rem;">
                                <option value="">Semua Jenis</option>
                                @foreach($letterTypes as $type)
                                    <option value="{{ $type->id }}" @selected(request('letter_type_id') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:0.75rem; display:flex; gap:0.5rem;">
                        <button type="submit" style="padding:0.5rem 1rem; background-color:#4f46e5; color:#ffffff; border:none; border-radius:0.375rem; font-size:0.875rem; font-weight:500; cursor:pointer;">Terapkan Filter</button>
                        @if(request()->hasAny(['team_leader','tanggal_dari','tanggal_sampai','letter_type_id']))
                            <a href="{{ route('letters.index') }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#e5e7eb; color:#374151; border-radius:0.375rem; font-size:0.875rem; font-weight:500; text-decoration:none;">Reset Filter</a>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400" style="margin-top:0.5rem;">Filter tanggal memakai tanggal surat. Tanpa filter tanggal, yang tampil adalah 30 hari terakhir.</p>
                </form>
                @endif

                <!-- Tabel Riwayat Surat -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nomor Surat</th>
                                <th class="px-4 py-3 text-center">Jenis</th>
                                <th class="px-4 py-3">Perihal & Tujuan</th>
                                <th class="px-4 py-3">Tgl Surat</th>
                                <th class="px-4 py-3">Pembuat</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($letters as $index => $letter)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $letters->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $letter->letter_number }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 {{ $letter->letterType?->code === 'SP1' ? 'bg-red-100 text-red-800' : 'bg-indigo-100 text-indigo-800' }} text-xs rounded font-medium">
                                            {{ $letter->letterType->code ?? '-' }}
                                        </span>
                                    </td>
                                    <!-- PERUBAHAN PERIHAL & TUJUAN DI SINI -->
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900">{{ $letter->letterType->name ?? $letter->subject }}</div>
                                        <div class="text-xs text-gray-600 mt-0.5">Kepada: <span class="font-semibold">{{ $letter->recipient }}</span></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($letter->letter_date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">{{ $letter->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center space-x-2 whitespace-nowrap">
                                        <!-- Tombol Preview -->
                                        <a href="{{ route('letters.preview', $letter->id) }}" 
                                           target="_blank" 
                                           class="px-3 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700 font-medium transition">
                                            Preview
                                        </a>
                                        <!-- Tombol Download PDF -->
                                        <a href="{{ route('letters.export-pdf', $letter->id) }}" 
                                           class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 font-medium transition">
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-10 h-10 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p>Belum ada riwayat surat dalam 30 hari terakhir.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="mt-4">
                    {{ $letters->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>