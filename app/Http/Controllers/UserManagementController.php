<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /** Daftar semua pengguna. */
    public function index()
    {
        $users = User::orderByRaw("FIELD(role, 'super_admin', 'department_head', 'team_leader')")
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    /** Form buat akun (Team Leader / Department Head). */
    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['team_leader', 'department_head'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')->with('success', 'Akun ' . $data['name'] . ' berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['team_leader', 'department_head', 'super_admin'])],
        ]);

        if ($user->id === auth()->id() && $data['role'] !== 'super_admin') {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.'])->withInput();
        }

        if ($user->isSuperAdmin() && $data['role'] !== 'super_admin'
            && User::where('role', 'super_admin')->count() <= 1) {
            return back()->withErrors(['role' => 'Tidak dapat mengubah Super Admin terakhir.'])->withInput();
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Akun ' . $user->name . ' berhasil diperbarui.');
    }

    /** Reset kata sandi pengguna lain. */
    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Kata sandi ' . $user->name . ' berhasil direset.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus Super Admin terakhir.']);
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun ' . $nama . ' berhasil dihapus.');
    }
}
