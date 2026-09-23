<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan form profil mandiri bagi Tendik.
     */
    public function edit()
    {
        $user = auth()->user();
        return view('tendik.profile', compact('user'));
    }

    /**
     * Simpan perubahan profil mandiri Tendik.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip'              => 'nullable|string|max:50',
            'no_hp'            => 'nullable|string|max:20',
            'jenis_kelamin'    => 'nullable|in:L,P',
            'tahun_masuk'      => 'nullable|string|max:10',
            'lulusan_tahun'    => 'nullable|string|max:10',
            'alamat'           => 'nullable|string',
            'desa'             => 'nullable|string|max:100',
            'kecamatan'        => 'nullable|string|max:100',
            'kabupaten'        => 'nullable|string|max:100',
            'provinsi'         => 'nullable|string|max:100',
            'pendidikan_sd'    => 'nullable|string|max:200',
            'tahun_lulus_sd'   => 'nullable|string|max:10',
            'pendidikan_smp'   => 'nullable|string|max:200',
            'tahun_lulus_smp'  => 'nullable|string|max:10',
            'pendidikan_sma'   => 'nullable|string|max:200',
            'tahun_lulus_sma'  => 'nullable|string|max:10',
            'pendidikan_s1'    => 'nullable|string|max:200',
            'tahun_lulus_s1'   => 'nullable|string|max:10',
            'pendidikan_s2'    => 'nullable|string|max:200',
            'tahun_lulus_s2'   => 'nullable|string|max:10',
            'password'         => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan oleh akun lain.',
            'password.min'      => 'Password baru minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password baru tidak sesuai.',
        ]);

        if (!empty($validated['password'])) {
            $plainPassword = $validated['password'];
            $validated['password'] = Hash::make($plainPassword);
            $validated['password_plain'] = $plainPassword;
        } else {
            unset($validated['password']);
        }

        $validated['nama_lengkap'] = $validated['name'];

        $user->update($validated);

        return redirect()->route('tendik.profile.edit')
            ->with('success', 'Profil dan riwayat pendidikan Anda berhasil diperbarui.');
    }
}
