<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard sadar-role (31 Okt 2026):
     * - super_admin     : hanya sapaan selamat datang.
     * - team_leader     : statistik & surat milik sendiri.
     * - department_head: statistik keseluruhan + produksi per Team Leader.
     */
    public function index()
    {
        $user = auth()->user();
        $namaPengguna = $user->name;
        $tanggalHariIni = Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y');

        if ($user->isSuperAdmin()) {
            return view('dashboard', [
                'mode' => 'welcome',
                'namaPengguna' => $namaPengguna,
                'tanggalHariIni' => $tanggalHariIni,
            ]);
        }

        $personal = $user->isTeamLeader();
        $scope = fn ($q) => $personal ? $q->where('user_id', $user->id) : $q;

        // Aplikasi berjalan dalam UTC; batas hari/bulan dihitung dalam WIB lalu dikonversi.
        $nowJakarta = Carbon::now('Asia/Jakarta');
        $todayStart = $nowJakarta->copy()->startOfDay()->utc();
        $todayEnd = $nowJakarta->copy()->endOfDay()->utc();
        $monthStart = $nowJakarta->copy()->startOfMonth()->utc();
        $monthEnd = $nowJakarta->copy()->endOfMonth()->utc();

        $suratHariIni = $scope(Letter::query())->whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $suratBulanIni = $scope(Letter::query())->whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $totalSurat = $scope(Letter::query())->count();

        // Tren 6 bulan terakhir (per bulan kalender WIB)
        $trenLabels = [];
        $trenValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $nowJakarta->copy()->subMonths($i);
            $trenLabels[] = $m->translatedFormat('M Y');
            $trenValues[] = $scope(Letter::query())
                ->whereBetween('created_at', [$m->copy()->startOfMonth()->utc(), $m->copy()->endOfMonth()->utc()])
                ->count();
        }

        // Distribusi per jenis surat (semua jenis tetap tampil, 0 bila kosong)
        $distribusi = LetterType::query()
            ->leftJoin('letters', function ($join) use ($personal, $user) {
                $join->on('letters.letter_type_id', '=', 'letter_types.id');
                if ($personal) {
                    $join->where('letters.user_id', $user->id);
                }
            })
            ->select('letter_types.name', DB::raw('COUNT(letters.id) as total'))
            ->groupBy('letter_types.id', 'letter_types.name')
            ->orderBy('letter_types.id')
            ->get();

        $suratTerbaru = $scope(Letter::with(['letterType', 'user']))->latest()->limit(8)->get();

        $data = [
            'mode' => $personal ? 'personal' : 'oversight',
            'namaPengguna' => $namaPengguna,
            'tanggalHariIni' => $tanggalHariIni,
            'jenisSurat' => LetterType::orderBy('id')->get(['id', 'name']),
            'suratHariIni' => $suratHariIni,
            'suratBulanIni' => $suratBulanIni,
            'totalSurat' => $totalSurat,
            'suratTerbaru' => $suratTerbaru,
            'trenLabels' => $trenLabels,
            'trenValues' => $trenValues,
            'distribusi' => $distribusi,
        ];

        if (! $personal) {
            $data['totalRekening'] = BankAccount::count();
            $data['totalTunggakan'] = (float) BankAccount::sum(DB::raw('principal_arrears + interest_arrears + penalty_fee'));
            $data['produksiPerTL'] = User::where('role', 'team_leader')->orderBy('name')->get()
                ->map(fn ($tl) => (object) [
                    'name' => $tl->name,
                    'email' => $tl->email,
                    'surat_bulan_ini' => Letter::where('user_id', $tl->id)->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'surat_total' => Letter::where('user_id', $tl->id)->count(),
                ]);
        }

        return view('dashboard', $data);
    }
}
