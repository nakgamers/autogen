<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <style>
        #dashRoot { padding-top: 2rem; padding-bottom: 2rem; }
        .dash-card { background:#fff; border-radius:0.5rem; box-shadow:0 1px 2px rgba(0,0,0,.08); padding:1.25rem; }
        .dash-stats { display:grid; grid-template-columns:repeat(5, 1fr); gap:1rem; }
        .dash-stats-3 { grid-template-columns:repeat(3, 1fr); }
        .dash-stats > div { background:#fff; border-radius:0.5rem; box-shadow:0 1px 2px rgba(0,0,0,.08); padding:1.25rem; }
        .dash-charts { display:grid; grid-template-columns:2fr 1fr; gap:1rem; }
        .dash-letters { background:#fff; border-radius:0.5rem; box-shadow:0 1px 2px rgba(0,0,0,.08); padding-top:1.25rem; padding-bottom:1.25rem; overflow:hidden; }
        @media (max-width: 900px) { .dash-stats, .dash-stats-3 { grid-template-columns:repeat(2, 1fr); } .dash-charts { grid-template-columns:1fr; } }
    </style>

    <div id="dashRoot">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Sapaan (semua role) --}}
            <div class="dash-card" style="margin-bottom:1.5rem;">
                <h3 class="text-lg font-semibold text-gray-800">Selamat datang, {{ $namaPengguna }} 👋</h3>
                <p class="text-sm text-gray-500">{{ $tanggalHariIni }} · {{ auth()->user()->roleLabel() }} · Bank Jakarta — Unit SAM / WOLSAM</p>
            </div>

            @if($mode !== 'welcome')

                @if($mode === 'personal')
                {{-- Aksi Cepat — Team Leader --}}
                <div class="dash-card" style="margin-bottom:1.5rem;">
                    <h3 class="text-sm font-semibold text-gray-700" style="margin-bottom:0.75rem;">Aksi Cepat — Buat Surat</h3>
                    <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                        @foreach($jenisSurat as $jenis)
                            <a href="{{ route('letters.create', ['jenis' => $jenis->id]) }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#4f46e5; color:#ffffff; border-radius:0.375rem; font-size:0.875rem; font-weight:500; text-decoration:none;">+ {{ $jenis->name }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Statistik personal --}}
                <div class="dash-stats dash-stats-3" style="margin-bottom:1.5rem;">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Surat Saya Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $suratHariIni }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Surat Saya Bulan Ini</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $suratBulanIni }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Surat Saya</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $totalSurat }}</p>
                    </div>
                </div>
                @endif

                @if($mode === 'oversight')
                {{-- Statistik keseluruhan — Department Head --}}
                <div class="dash-stats" style="margin-bottom:1.5rem;">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Surat Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $suratHariIni }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Surat Bulan Ini</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $suratBulanIni }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Surat</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $totalSurat }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Rekening</p>
                        <p class="text-3xl font-bold text-gray-800" style="margin-top:0.25rem;">{{ $totalRekening }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Tunggakan</p>
                        <p class="text-2xl font-bold text-gray-800" style="margin-top:0.25rem;">Rp{{ number_format($totalTunggakan, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Produksi per Team Leader --}}
                <div class="dash-card" style="margin-bottom:1.5rem;">
                    <h3 class="text-sm font-semibold text-gray-700" style="margin-bottom:0.75rem;">Produksi per Team Leader</h3>
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead>
                            <tr style="border-bottom:2px solid #e5e7eb;">
                                <th class="py-2">Team Leader</th>
                                <th class="py-2">Email</th>
                                <th class="py-2" style="text-align:center;">Surat Bulan Ini</th>
                                <th class="py-2" style="text-align:center;">Total Surat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produksiPerTL as $tl)
                                <tr style="border-bottom:1px solid #f3f4f6;">
                                    <td class="py-2 font-medium text-gray-800">{{ $tl->name }}</td>
                                    <td class="py-2">{{ $tl->email }}</td>
                                    <td class="py-2" style="text-align:center;">{{ $tl->surat_bulan_ini }}</td>
                                    <td class="py-2" style="text-align:center;">{{ $tl->surat_total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-3 text-center text-gray-400">Belum ada akun Team Leader.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Grafik --}}
                <div class="dash-charts" style="margin-bottom:1.5rem;">
                    <div class="dash-card">
                        <h3 class="text-sm font-semibold text-gray-700" style="margin-bottom:0.75rem;">Tren Surat — 6 Bulan Terakhir</h3>
                        <div style="height:260px;"><canvas id="chartTren"></canvas></div>
                    </div>
                    <div class="dash-card">
                        <h3 class="text-sm font-semibold text-gray-700" style="margin-bottom:0.75rem;">Distribusi per Jenis Surat</h3>
                        <div style="height:260px;"><canvas id="chartDistribusi"></canvas></div>
                    </div>
                </div>

                {{-- Surat Terbaru --}}
                <div class="dash-letters">
                    <h3 class="text-sm font-semibold text-gray-700" style="padding-left:1.25rem; padding-right:1.25rem; margin-bottom:0.75rem;">{{ $mode === 'personal' ? 'Surat Terbaru Saya' : 'Surat Terbaru' }}</h3>
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead>
                            <tr style="border-bottom:2px solid #e5e7eb;">
                                <th class="py-2" style="padding-left:1.25rem;">Nomor Surat</th>
                                <th class="py-2">Jenis</th>
                                <th class="py-2">Perihal</th>
                                <th class="py-2">Tanggal</th>
                                <th class="py-2">Dibuat Oleh</th>
                                <th class="py-2" style="padding-right:1.25rem; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suratTerbaru as $surat)
                                <tr style="border-bottom:1px solid #f3f4f6;">
                                    <td class="py-2 font-medium text-gray-800" style="padding-left:1.25rem;">{{ $surat->letter_number }}</td>
                                    <td class="py-2">{{ $surat->letterType->name ?? '-' }}</td>
                                    <td class="py-2">{{ \Illuminate\Support\Str::limit($surat->subject, 40) }}</td>
                                    <td class="py-2">{{ \Carbon\Carbon::parse($surat->letter_date)->format('d M Y') }}</td>
                                    <td class="py-2">{{ $surat->user->name ?? '-' }}</td>
                                    <td class="py-2" style="padding-right:1.25rem; text-align:center; white-space:nowrap;">
                                        <a href="{{ route('letters.preview', $surat->id) }}" target="_blank" style="color:#4f46e5; font-weight:500; text-decoration:none;">Preview</a>
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('letters.export-pdf', $surat->id) }}" style="color:#059669; font-weight:500; text-decoration:none;">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-4 text-center text-gray-400" style="padding-left:1.25rem;">Belum ada surat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @endif
        </div>
    </div>

    @if($mode !== 'welcome')
    <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
    <script>
        const trenLabels = @json($trenLabels);
        const trenValues = @json($trenValues);
        new Chart(document.getElementById('chartTren'), {
            type: 'bar',
            data: { labels: trenLabels, datasets: [{ label: 'Surat terbit', data: trenValues, backgroundColor: '#4f46e5', borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
        const distLabels = @json($distribusi->pluck('name'));
        const distValues = @json($distribusi->pluck('total'));
        new Chart(document.getElementById('chartDistribusi'), {
            type: 'doughnut',
            data: { labels: distLabels, datasets: [{ data: distValues, backgroundColor: ['#4f46e5','#7c3aed','#059669','#0d9488','#d97706','#dc2626','#2563eb','#db2777','#65a30d'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
        });
    </script>
    @endif
</x-app-layout>
