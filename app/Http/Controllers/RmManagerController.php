<?php

namespace App\Http\Controllers;

use App\Models\RmManager;
use Illuminate\Http\Request;

class RmManagerController extends Controller
{
    /** Daftar RM Manager (nama & nomor telepon). */
    public function index(Request $request)
    {
        $query = RmManager::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $rmManagers = $query->paginate(20)->withQueryString();

        return view('rm-managers.index', compact('rmManagers'));
    }

    public function edit(RmManager $rmManager)
    {
        return view('rm-managers.edit', compact('rmManager'));
    }

    public function update(Request $request, RmManager $rmManager)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        $rmManager->update($data);

        return redirect()->route('rm-managers.index')
            ->with('success', 'Data RM ' . $rmManager->name . ' berhasil diperbarui.');
    }
}
