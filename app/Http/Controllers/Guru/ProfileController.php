<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Guru;
use App\Models\User;
use App\Models\PengaturanSekolah;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman formulir ubah profil guru.
     */
    public function edit()
    {
        $user = auth()->user();
        abort_if(!$user->isGuru(), 403, 'Akses khusus dewan guru.');

        // Ambil data profil dari tabel gurus
        $guruHilal = Guru::where('user_id', $user->id)->first();
        if (!$guruHilal) {
            // Fallback pencarian dengan NIP atau email jika user_id belum terkait
            $guruHilal = Guru::where(function($q) use ($user) {
                if ($user->nip) $q->where('nip', $user->nip);
                if ($user->email) $q->orWhere('email', $user->email);
            })->first();
        }

        // Ambil mapel dari jadwal
        $mapelDiampu = \App\Models\JadwalPelajaran::where('guru_user_id', $user->id)
            ->join('mata_pelajarans', 'jadwal_pelajarans.mata_pelajaran_id', '=', 'mata_pelajarans.id')
            ->distinct()
            ->pluck('mata_pelajarans.nama')
            ->toArray();
        $bidangStudiJadwal = !empty($mapelDiampu) ? implode(', ', $mapelDiampu) : null;

        // $user sudah merupakan instance User dari db_hilaledu
        $userHilal = $user;

        return view('guru.profile', compact('user', 'guruHilal', 'userHilal', 'bidangStudiJadwal'));
    }

    /**
     * Simpan pembaruan profil guru ke db_akademik dan db_hilaledu secara bersamaan.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        abort_if(!$user->isGuru(), 403, 'Akses khusus dewan guru.');

        $validated = $request->validate([
            // Identitas Utama
            'name'                => 'required|string|max:255',
            'username'            => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'email'               => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip'                 => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'nuptk'               => 'nullable|string|max:50',
            'jenis_kelamin'       => 'nullable|in:L,P',
            'tempat_lahir'        => 'nullable|string|max:100',
            'tanggal_lahir'       => 'nullable|date',
            // Kepegawaian & Penugasan
            'jabatan_utama'       => 'nullable|string|max:100',
            'bidang_studi'        => 'nullable|string|max:100',
            'status_kepegawaian'  => 'nullable|string|max:50',
            // Kontak & Alamat Lengkap
            'no_hp'               => 'nullable|string|max:30',
            'alamat'              => 'nullable|string',
            'desa'                => 'nullable|string|max:100',
            'kecamatan'           => 'nullable|string|max:100',
            'kabupaten'           => 'nullable|string|max:100',
            'provinsi'            => 'nullable|string|max:100',
            // Riwayat Pendidikan Formal
            'pendidikan_sd'       => 'nullable|string|max:150',
            'tahun_lulus_sd'      => 'nullable|string|max:10',
            'pendidikan_smp'      => 'nullable|string|max:150',
            'tahun_lulus_smp'     => 'nullable|string|max:10',
            'pendidikan_sma'      => 'nullable|string|max:150',
            'tahun_lulus_sma'     => 'nullable|string|max:10',
            'pendidikan_s1'       => 'nullable|string|max:150',
            'tahun_lulus_s1'      => 'nullable|string|max:10',
            'pendidikan_s2'       => 'nullable|string|max:150',
            'tahun_lulus_s2'      => 'nullable|string|max:10',
            // Foto Profil & Keamanan
            'foto_file'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password'            => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'       => 'Nama lengkap wajib diisi.',
            'username.required'   => 'Username wajib diisi.',
            'username.unique'     => 'Username tersebut sudah digunakan oleh akun lain.',
            'email.required'      => 'Email wajib diisi.',
            'email.unique'        => 'Email tersebut sudah digunakan oleh akun lain.',
            'nip.unique'          => 'NIP tersebut sudah terdaftar pada guru lain.',
            'foto_file.max'       => 'Ukuran file foto maksimal 2 MB.',
            'password.min'        => 'Password baru minimal 6 karakter.',
            'password.confirmed'  => 'Konfirmasi password baru tidak cocok.',
        ]);

        // 1. Handle Upload Foto Profil jika ada
        $fotoRelPath = null;
        if ($request->hasFile('foto_file')) {
            $file = $request->file('foto_file');
            $filename = 'guru_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            $targetDir = public_path('uploads/avatars');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $file->move($targetDir, $filename);
            $fotoRelPath = 'uploads/avatars/' . $filename;
        }

        // 2. Susun Data untuk db_akademik.users
        $validated['nama_lengkap'] = $validated['name'];
        if ($fotoRelPath) {
            $validated['foto'] = $fotoRelPath;
            $validated['avatar'] = $fotoRelPath;
        }

        if (!empty($validated['password'])) {
            $validated['password_plain'] = $validated['password'];
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Simpan ke db_akademik.users
        $user->update($validated);

        // 3. Sinkronisasi ke db_hilaledu.gurus
        try {
            // Normalisasi status kepegawaian untuk ENUM db_hilaledu.gurus: ('PNS','non-PNS','honorer','kontrak')
            $statusHilalEnum = match($validated['status_kepegawaian'] ?? 'non-PNS') {
                'PNS' => 'PNS',
                'honorer', 'GTT', 'Guru Pesantren' => 'honorer',
                'kontrak' => 'kontrak',
                default => 'non-PNS',
            };

            $guruData = [
                'user_id'            => $user->id,
                'nip'                => $validated['nip'] ?? null,
                'nuptk'              => $validated['nuptk'] ?? null,
                'nama_lengkap'       => $validated['name'],
                'jenis_kelamin'      => in_array($validated['jenis_kelamin'] ?? '', ['L', 'P']) ? $validated['jenis_kelamin'] : 'L',
                'tempat_lahir'       => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir'      => $validated['tanggal_lahir'] ?? null,
                'alamat'             => $validated['alamat'] ?? null,
                'no_hp'              => $validated['no_hp'] ?? null,
                'email'              => $validated['email'],
                'jabatan'            => $validated['jabatan_utama'] ?? null,
                'bidang_studi'       => $validated['bidang_studi'] ?? null,
                'status_kepegawaian' => $statusHilalEnum,
                'is_active'          => 1,
            ];

            if ($fotoRelPath) {
                $guruData['foto'] = $fotoRelPath;
            }

            Guru::updateOrCreate(
                ['user_id' => $user->id],
                $guruData
            );
        } catch (\Throwable $e) {
            // Abaikan jika tabel eksternal hilaledu sedang offline
        }

        // 4. Sinkronisasi ke db_hilaledu.users
        try {
            $hilalUserUpdate = [
                'name'              => $validated['name'],
                'nama_lengkap'      => $validated['name'],
                'username'          => $validated['username'],
                'email'             => $validated['email'],
                'nip'               => $validated['nip'] ?? null,
                'jenis_kelamin'     => $validated['jenis_kelamin'] ?? null,
                'no_hp'             => $validated['no_hp'] ?? null,
                'alamat'            => $validated['alamat'] ?? null,
                'desa'              => $validated['desa'] ?? null,
                'kecamatan'         => $validated['kecamatan'] ?? null,
                'kabupaten'         => $validated['kabupaten'] ?? null,
                'provinsi'          => $validated['provinsi'] ?? null,
                'jabatan_utama'     => $validated['jabatan_utama'] ?? null,
                'pendidikan_sd'     => $validated['pendidikan_sd'] ?? null,
                'tahun_lulus_sd'    => $validated['tahun_lulus_sd'] ?? null,
                'pendidikan_smp'    => $validated['pendidikan_smp'] ?? null,
                'tahun_lulus_smp'   => $validated['tahun_lulus_smp'] ?? null,
                'pendidikan_sma'    => $validated['pendidikan_sma'] ?? null,
                'tahun_lulus_sma'   => $validated['tahun_lulus_sma'] ?? null,
                'pendidikan_s1'     => $validated['pendidikan_s1'] ?? null,
                'tahun_lulus_s1'    => $validated['tahun_lulus_s1'] ?? null,
                'pendidikan_s2'     => $validated['pendidikan_s2'] ?? null,
                'tahun_lulus_s2'    => $validated['tahun_lulus_s2'] ?? null,
                'updated_at'        => now(),
            ];

            if ($fotoRelPath) {
                $hilalUserUpdate['foto'] = $fotoRelPath;
                $hilalUserUpdate['avatar'] = $fotoRelPath;
            }

            if (!empty($request->password)) {
                $hilalUserUpdate['password'] = Hash::make($request->password);
                $hilalUserUpdate['password_plain'] = $request->password;
            }

            $user->update($hilalUserUpdate);
        } catch (\Throwable $e) {
            // Abaikan jika pembaruan profil pelengkap gagal
        }

        // 5. Update nama guru di master_penugasan_guru jika ada
        try {
            $masterGuru = PengaturanSekolah::get('master_penugasan_guru', []);
            $changed = false;
            foreach ($masterGuru as $kode => &$info) {
                if (!empty($info['user_id']) && (int)$info['user_id'] === (int)$user->id) {
                    $info['nama'] = $validated['name'];
                    if (!empty($validated['bidang_studi'])) {
                        $info['mapel'] = $validated['bidang_studi'];
                    }
                    if (!empty($validated['jabatan_utama'])) {
                        $info['jabatan'] = $validated['jabatan_utama'];
                    }
                    $changed = true;
                    break;
                }
            }
            if ($changed) {
                PengaturanSekolah::set('master_penugasan_guru', $masterGuru, 'Master Penugasan Guru', 'json');
            }
        } catch (\Throwable $e) {}

        return redirect()->route('guru.profile.edit')
            ->with('success', 'Profil guru berhasil diperbarui dan telah disinkronkan ke seluruh sistem HilalEdu.');
    }
}
