<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil siswa untuk edit mandiri.
     */
    public function edit()
    {
        $user = auth()->user();
        abort_if($user->role !== 'siswa', 403);

        return view('siswa.profile', compact('user'));
    }

    /**
     * Simpan pembaruan profil siswa secara mandiri.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        abort_if($user->role !== 'siswa', 403);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip'              => ['nullable', 'string', 'max:30'],
            'no_hp'            => 'nullable|string|max:20',
            'jenis_kelamin'    => 'nullable|in:L,P',
            // Alamat
            'alamat'           => 'nullable|string',
            'desa'             => 'nullable|string|max:100',
            'kecamatan'        => 'nullable|string|max:100',
            'kabupaten'        => 'nullable|string|max:100',
            'provinsi'         => 'nullable|string|max:100',
            // Data Orang Tua / Wali
            'nama_ayah'        => 'nullable|string|max:150',
            'nama_ibu'         => 'nullable|string|max:150',
            'no_hp_ortu'       => 'nullable|string|max:30',
            // Riwayat Pendidikan
            'pendidikan_sd'    => 'nullable|string|max:150',
            'tahun_lulus_sd'   => 'nullable|string|max:10',
            'pendidikan_smp'   => 'nullable|string|max:150',
            'tahun_lulus_smp'  => 'nullable|string|max:10',
            // Password baru opsional
            'password'         => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'password.min'      => 'Password baru minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $validated['nama_lengkap'] = $validated['name'];

        if (!empty($validated['password'])) {
            $validated['password_plain'] = $validated['password'];
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('siswa.profile.edit')
            ->with('success', 'Biodata dan data diri Anda berhasil diperbarui.');
    }
}
