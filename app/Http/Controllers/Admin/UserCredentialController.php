<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserCredentialController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show edit form
     */
    public function edit(User $user)
    {
        $user->load('roles');
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user credentials
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'nama_lengkap' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'is_active' => $request->has('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // If user is siswa, also update siswa nama_lengkap
        if ($user->hasRole('siswa') && $user->siswa) {
            $user->siswa->update(['nama_lengkap' => $validated['nama_lengkap']]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun {$user->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Reset password to default
     */
    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('password'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password {$user->nama_lengkap} berhasil direset ke 'password'.");
    }

    /**
     * Export user credentials to CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->orderBy('nama_lengkap')->get();

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Nama Lengkap', 'Username', 'Email', 'Role', 'Password Default', 'Status'
            ]);

            foreach ($users as $u) {
                fputcsv($handle, [
                    $u->nama_lengkap,
                    $u->name,
                    $u->email,
                    $u->roles->pluck('name')->join(', '),
                    'password',
                    $u->is_active ? 'Aktif' : 'Tidak Aktif',
                ]);
            }

            fclose($handle);
        }, 'kredensial_user_' . date('Y-m-d') . '.csv');
    }
}
