<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Tendik;
use App\Models\Kelas;
use App\Models\PengelolaAkademik;
use App\Http\Controllers\Admin\JadwalMatrixController;

class DirectoryController extends Controller
{
    /**
     * Direktori Pemantauan Data Guru Resmi (29 Guru)
     */
    public function gurus(Request $request)
    {
        $search = $request->query('q');
        $tugasFilter = $request->query('tugas');

        $query = User::where('role', 'guru')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tugasFilter) {
            $query->where('tugas_tambahan', 'like', "%{$tugasFilter}%");
        }

        $gurus = $query->paginate(50)->withQueryString();

        // Mapping kode guru 1 - 30 dari dokumen matriks
        $masterGuruKode = JadwalMatrixController::getDaftarGuruResmi();
        $kodeByNama = [];
        foreach ($masterGuruKode as $kode => $info) {
            $kodeByNama[strtolower(trim($info['nama']))] = $kode;
        }

        $stats = [
            'total'          => User::where('role', 'guru')->count(),
            'wali_kelas'     => User::where('role', 'guru')->where('tugas_tambahan', 'like', '%Wali Kelas%')->count(),
            'manajemen_waka' => User::where('role', 'guru')->where(function($q) {
                $q->where('tugas_tambahan', 'like', '%Wakasek%')
                  ->orWhere('tugas_tambahan', 'like', '%Kepala%')
                  ->orWhere('jabatan_utama', 'like', '%Kepala%');
            })->count(),
            'kaprog'         => User::where('role', 'guru')->where('tugas_tambahan', 'like', '%Kaprog%')->count(),
        ];

        $daftarTugasTambahan = GuruController::getDaftarTugasTambahan();

        return view('admin.directory.gurus', compact('gurus', 'masterGuruKode', 'kodeByNama', 'stats', 'search', 'tugasFilter', 'daftarTugasTambahan'));
    }

    /**
     * Normalisasi status kepegawaian untuk enum db_hilaledu (PNS, non-PNS, honorer, kontrak)
     */
    private function mapStatusKepegawaianHilal(?string $status): string
    {
        if (!$status) return 'non-PNS';
        $s = strtolower(trim($status));
        if (str_contains($s, 'pns') && !str_contains($s, 'non')) {
            return 'PNS';
        }
        if (str_contains($s, 'honor') || str_contains($s, 'gtt')) {
            return 'honorer';
        }
        if (str_contains($s, 'kontrak')) {
            return 'kontrak';
        }
        return 'non-PNS';
    }

    /**
     * Update data guru langsung tersinkron ke db_hilaledu.gurus dan users
     */
    public function updateGuru(Request $request, User $user)
    {
        abort_if(!auth()->user()->canManageAcademic(), 403, 'Anda tidak memiliki hak akses mengedit data guru.');

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'username'           => 'required|string|max:100|unique:users,username,' . $user->id,
            'email'              => 'required|email|max:255|unique:users,email,' . $user->id,
            'nip'                => 'nullable|string|max:50',
            'nuptk'              => 'nullable|string|max:50',
            'no_hp'              => 'nullable|string|max:30',
            'jenis_kelamin'      => 'nullable|in:L,P',
            'jabatan_utama'      => 'nullable|string|max:150',
            'tugas_tambahan'     => 'nullable|array',
            'bidang_studi'       => 'nullable|string|max:150',
            'status_kepegawaian' => 'nullable|string|max:50',
            'is_active'          => 'nullable',
            'password'           => 'nullable|string|min:6',
        ]);

        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;
        $tugasClean = array_values(array_filter($validated['tugas_tambahan'] ?? []));

        // 1. Update Database Lokal (db_akademik.users)
        $localData = [
            'name'               => $validated['name'],
            'nama_lengkap'       => $validated['name'],
            'username'           => $validated['username'],
            'email'              => $validated['email'],
            'nip'                => $validated['nip'] ?? null,
            'nuptk'              => $validated['nuptk'] ?? null,
            'no_hp'              => $validated['no_hp'] ?? null,
            'jenis_kelamin'      => $validated['jenis_kelamin'] ?? null,
            'jabatan_utama'      => $validated['jabatan_utama'] ?: 'Guru',
            'tugas_tambahan'     => json_encode($tugasClean),
            'bidang_studi'       => $validated['bidang_studi'] ?? null,
            'status_kepegawaian' => $validated['status_kepegawaian'] ?? null,
            'is_active'          => $isActive,
        ];

        if (!empty($validated['password'])) {
            $localData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
            $localData['password_plain'] = $validated['password'];
        }

        $user->update($localData);

        // 2. Pembaruan tabel gurus lokal pada db_hilaledu
        try {
            $statusHilal = $this->mapStatusKepegawaianHilal($validated['status_kepegawaian'] ?? null);
            \App\Models\Guru::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'                => $validated['nip'] ?? null,
                    'nuptk'              => $validated['nuptk'] ?? null,
                    'nama_lengkap'       => $validated['name'],
                    'jenis_kelamin'      => $validated['jenis_kelamin'] ?? null,
                    'no_hp'              => $validated['no_hp'] ?? null,
                    'email'              => $validated['email'],
                    'jabatan'            => $validated['jabatan_utama'] ?: 'Guru',
                    'bidang_studi'       => $validated['bidang_studi'] ?? null,
                    'status_kepegawaian' => $statusHilal,
                    'is_active'          => $isActive ? 1 : 0,
                    'updated_at'         => now(),
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal update guru profil: " . $e->getMessage());
        }

        $msg = "Data guru {$user->name} berhasil diperbarui" . ($syncedHilal ? " dan tersinkron langsung ke database HilalEdu." : ".");
        return redirect()->back()->with('success', $msg);
    }

    /**
     * Update data siswa langsung tersinkron ke db_hilaledu.siswas dan users
     */
    public function updateSiswa(Request $request, Siswa $siswa)
    {
        abort_if(!auth()->user()->canManageAcademic(), 403, 'Anda tidak memiliki hak akses mengedit data siswa.');

        $validated = $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'nis'           => 'required|string|max:50',
            'nisn'          => 'nullable|string|max:50',
            'kelas_id'      => 'nullable|integer',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tempat_lahir'  => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat'        => 'nullable|string',
            'no_hp'         => 'nullable|string|max:30',
            'nama_wali'     => 'nullable|string|max:150',
            'no_hp_wali'    => 'nullable|string|max:30',
            'status'        => 'nullable|string|max:30',
        ]);

        // Pastikan status cocok dengan enum db_hilaledu.siswas ('aktif','tidak_aktif','lulus','pindah')
        $statusSiswa = $validated['status'] ?? 'aktif';
        if ($statusSiswa === 'keluar') {
            $statusSiswa = 'tidak_aktif';
        }
        $validated['status'] = $statusSiswa;

        $siswa->update($validated);

        // Sinkronisasi ke akun users jika ada user_id
        // Sinkronisasi ke akun users jika ada user_id
        if ($siswa->user_id) {
            try {
                User::where('id', $siswa->user_id)->update([
                    'name'         => $validated['nama_lengkap'],
                    'nama_lengkap' => $validated['nama_lengkap'],
                    'kelas_id'     => $validated['kelas_id'] ?? null,
                    'no_hp'        => $validated['no_hp'] ?? null,
                    'nis'          => $validated['nis'],
                    'nisn'         => $validated['nisn'] ?? null,
                    'alamat'       => $validated['alamat'] ?? null,
                    'updated_at'   => now(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal sinkron siswa user: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Data siswa {$siswa->nama_lengkap} berhasil diperbarui langsung pada database HilalEdu.");
    }

    /**
     * Update data tendik langsung tersinkron ke db_hilaledu.tendiks dan users
     */
    public function updateTendik(Request $request, Tendik $tendik)
    {
        abort_if(!auth()->user()->canManageAcademic(), 403, 'Anda tidak memiliki hak akses mengedit data tendik.');

        $validated = $request->validate([
            'nama_lengkap'       => 'required|string|max:255',
            'nuptk'              => 'nullable|string|max:50',
            'nip'                => 'nullable|string|max:50',
            'no_hp'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:255',
            'jenis_kelamin'      => 'nullable|in:L,P',
            'jabatan'            => 'nullable|string|max:150',
            'tugas'              => 'nullable|string|max:150',
            'bagian'             => 'nullable|string|max:150',
            'status_kepegawaian' => 'nullable|string|max:50',
            'is_active'          => 'nullable',
            'password'           => 'nullable|string|min:6',
        ]);

        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;
        $nipVal = $validated['nip'] ?? $validated['nuptk'] ?? null;
        $bagianVal = $validated['bagian'] ?? $validated['tugas'] ?? null;
        $statusHilal = $this->mapStatusKepegawaianHilal($validated['status_kepegawaian'] ?? null);
        $originalStatus = $validated['status_kepegawaian'] ?? null;

        $tendik->update([
            'nama_lengkap'       => $validated['nama_lengkap'],
            'nip'                => $nipVal,
            'no_hp'              => $validated['no_hp'] ?? null,
            'email'              => $validated['email'] ?? null,
            'jenis_kelamin'      => $validated['jenis_kelamin'] ?? null,
            'jabatan'            => $validated['jabatan'] ?? null,
            'bagian'             => $bagianVal,
            'status_kepegawaian' => $statusHilal,
            'is_active'          => $isActive ? 1 : 0,
        ]);

        // Jika memiliki user_id, update akun di hilaledu.users dan lokal users
        // Jika memiliki user_id, update akun di tabel users
        if ($tendik->user_id) {
            try {
                $userUpdates = [
                    'name'               => $validated['nama_lengkap'],
                    'nama_lengkap'       => $validated['nama_lengkap'],
                    'email'              => $validated['email'] ?? null,
                    'no_hp'              => $validated['no_hp'] ?? null,
                    'nip'                => $validated['nip'] ?? $validated['nuptk'] ?? null,
                    'jabatan_utama'      => $validated['jabatan'] ?? null,
                    'status_kepegawaian' => $originalStatus,
                    'is_active'          => $isActive ? 1 : 0,
                    'updated_at'         => now(),
                ];

                if (!empty($validated['password'])) {
                    $userUpdates['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
                    $userUpdates['password_plain'] = $validated['password'];
                }

                User::where('id', $tendik->user_id)->update($userUpdates);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal sinkron akun tendik: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Data tenaga kependidikan {$tendik->nama_lengkap} berhasil diperbarui langsung pada database HilalEdu.");
    }

    /**
     * Direktori Pemantauan Data Siswa Terdaftar (220 Siswa)
     */
    public function siswas(Request $request)
    {
        $search = $request->query('q');
        $kelasFilter = $request->query('kelas_id');

        $query = Siswa::with('kelas')->orderBy('nama_lengkap');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($kelasFilter) {
            $query->where('kelas_id', $kelasFilter);
        }

        $siswas = $query->paginate(25)->withQueryString();
        $kelasList = Kelas::orderBy('nama')->get();

        $stats = [
            'total'     => Siswa::count(),
            'laki'      => Siswa::where('jenis_kelamin', 'L')->count(),
            'perempuan' => Siswa::where('jenis_kelamin', 'P')->count(),
        ];

        return view('admin.directory.siswas', compact('siswas', 'kelasList', 'stats', 'search', 'kelasFilter'));
    }

    /**
     * Direktori Pemantauan Tenaga Kependidikan (Tendik)
     */
    public function tendiks(Request $request)
    {
        $search = $request->query('q');

        $query = Tendik::orderBy('nama_lengkap');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $tendiks = $query->get();

        // ID tendik yang ditunjuk sebagai Pengelola Akademik
        $pengelolaUserIds = PengelolaAkademik::pluck('user_id')->toArray();

        return view('admin.directory.tendiks', compact('tendiks', 'pengelolaUserIds', 'search'));
    }
}
