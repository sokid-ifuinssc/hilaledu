<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RombelController extends Controller
{
    /**
     * Overview Pengaturan Rombel per Kelas
     */
    public function index(Request $request)
    {
        $query = Kelas::with(['jurusan', 'waliKelasGuru'])->withCount('siswas');

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%$s%")
                  ->orWhere('nama_kelas', 'like', "%$s%")
                  ->orWhere('wali_kelas', 'like', "%$s%");
            });
        }

        $kelasList = $query->get()->sortBy('nama_lengkap');

        $totalSiswa       = User::where('role', 'siswa')->count();
        $siswaPunyaKelas  = User::where('role', 'siswa')->whereNotNull('kelas_id')->count();
        $siswaTanpaKelas  = User::where('role', 'siswa')->whereNull('kelas_id')->count();
        $jurusanList      = \App\Models\Jurusan::orderBy('nama')->get();

        return view('admin.rombel.index', compact(
            'kelasList', 'totalSiswa', 'siswaPunyaKelas', 'siswaTanpaKelas', 'jurusanList'
        ));
    }

    /**
     * Detail Rombel Kelas & Manajemen Siswa Anggota
     */
    public function show(Kelas $kela)
    {
        $kela->load(['jurusan', 'waliKelasGuru']);
        $siswas = User::where('role', 'siswa')
            ->where('kelas_id', $kela->id)
            ->orderBy('name')
            ->get();

        $siswaBelumAdaKelas = User::where('role', 'siswa')
            ->whereNull('kelas_id')
            ->orderBy('name')
            ->get();

        $kelasLain = Kelas::with('jurusan')
            ->where('id', '!=', $kela->id)
            ->get()
            ->sortBy('nama_lengkap');

        return view('admin.rombel.show', compact('kela', 'siswas', 'siswaBelumAdaKelas', 'kelasLain'));
    }

    /**
     * Masukkan Siswa (Satu atau Banyak) ke Rombel Kelas Ini
     */
    public function assign(Request $request, Kelas $kela)
    {
        $request->validate([
            'siswa_ids'   => 'required|array',
            'siswa_ids.*' => 'exists:users,id',
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa untuk dimasukkan ke rombel ini.',
        ]);

        $siswaIds = $request->siswa_ids;

        User::whereIn('id', $siswaIds)
            ->where('role', 'siswa')
            ->update(['kelas_id' => $kela->id]);

        if (Schema::hasTable('siswas')) {
            foreach ($siswaIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $identifier = $user->nip ?: ($user->username ?: '0');
                    DB::table('siswas')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'kelas_id'      => $kela->id,
                            'nama_lengkap'  => $user->name,
                            'nis'           => $identifier,
                            'nisn'          => $identifier,
                            'jenis_kelamin' => 'L',
                            'updated_at'    => now(),
                        ]
                    );
                }
            }
        }

        $count = count($siswaIds);
        return redirect()->route('superadmin.rombel.show', $kela)
            ->with('success', "Berhasil menambahkan {$count} siswa ke rombel {$kela->nama_lengkap}.");
    }

    /**
     * Keluarkan Siswa dari Rombel Kelas
     */
    public function remove(Request $request, Kelas $kela, User $user)
    {
        abort_if($user->role !== 'siswa', 404);

        if ($user->kelas_id == $kela->id) {
            $user->update(['kelas_id' => null]);

            if (Schema::hasTable('siswas')) {
                DB::table('siswas')->where('user_id', $user->id)->update(['kelas_id' => null]);
            }
        }

        return redirect()->route('superadmin.rombel.show', $kela)
            ->with('success', "Siswa {$user->name} berhasil dikeluarkan dari rombel {$kela->nama_lengkap}.");
    }

    /**
     * Keluarkan Banyak Siswa Terpilih sekaligus dari Rombel Kelas
     */
    public function removeBulk(Request $request, Kelas $kela)
    {
        $request->validate([
            'siswa_ids'   => 'required|array',
            'siswa_ids.*' => 'exists:users,id',
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa yang ingin dikeluarkan dari rombel.',
        ]);

        $siswaIds = $request->siswa_ids;

        User::whereIn('id', $siswaIds)
            ->where('role', 'siswa')
            ->where('kelas_id', $kela->id)
            ->update(['kelas_id' => null]);

        if (Schema::hasTable('siswas')) {
            DB::table('siswas')->whereIn('user_id', $siswaIds)->update(['kelas_id' => null]);
        }

        $count = count($siswaIds);
        return redirect()->route('superadmin.rombel.show', $kela)
            ->with('success', "Berhasil mengeluarkan {$count} siswa dari rombel {$kela->nama_lengkap}.");
    }

    /**
     * Pindahkan Siswa Terpilih dari Suatu Kelas ke Kelas Target
     */
    public function pindah(Request $request)
    {
        $request->validate([
            'siswa_ids'       => 'required|array',
            'siswa_ids.*'     => 'exists:users,id',
            'target_kelas_id' => 'required|exists:kelas,id',
        ], [
            'siswa_ids.required'       => 'Pilih minimal satu siswa yang ingin dipindahkan.',
            'target_kelas_id.required' => 'Pilih kelas tujuan pemindahan.',
        ]);

        $targetKelas = Kelas::findOrFail($request->target_kelas_id);
        $siswaIds    = $request->siswa_ids;

        User::whereIn('id', $siswaIds)
            ->where('role', 'siswa')
            ->update(['kelas_id' => $targetKelas->id]);

        if (Schema::hasTable('siswas')) {
            foreach ($siswaIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $identifier = $user->nip ?: ($user->username ?: '0');
                    DB::table('siswas')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'kelas_id'     => $targetKelas->id,
                            'nama_lengkap' => $user->name,
                            'nis'          => $identifier,
                            'nisn'         => $identifier,
                            'updated_at'   => now(),
                        ]
                    );
                }
            }
        }

        $count = count($siswaIds);
        return redirect()->back()
            ->with('success', "Berhasil memindahkan {$count} siswa ke kelas {$targetKelas->nama_lengkap}.");
    }
}
