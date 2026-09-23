<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PetugasPiket;
use App\Models\PengaturanSekolah;

class AdminPiketController extends Controller
{
    /**
     * Tampilkan Matriks Penugasan Petugas Piket (Tendik & Guru)
     */
    public function index(Request $request)
    {
        $tahunAjaran = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        $pikets = PetugasPiket::with('user', 'ditugaskanOleh')
            ->where('is_aktif', true)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->get();

        $piketsByHari = $pikets->groupBy('hari');

        // Daftar personil yang bisa ditugaskan: Seluruh Tendik dan Guru
        $tendiks = User::where('role', 'tendik')->where('is_active', true)->orderBy('name')->get();
        $gurus = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();

        return view('admin.piket.index', compact(
            'pikets',
            'piketsByHari',
            'tendiks',
            'gurus',
            'tahunAjaran',
            'semester'
        ));
    }

    /**
     * Simpan penugasan petugas piket baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'hari'       => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'lokasi_pos' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tahunAjaran = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        // Cek duplikasi
        $exists = PetugasPiket::where('user_id', $request->user_id)
            ->where('hari', $request->hari)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        if ($exists) {
            return back()->with('warning', 'Personil tersebut sudah ditugaskan pada hari ' . $request->hari . '.');
        }

        PetugasPiket::create([
            'user_id'                 => $request->user_id,
            'hari'                    => $request->hari,
            'tahun_ajaran'            => $tahunAjaran,
            'semester'                => $semester,
            'lokasi_pos'              => $request->lokasi_pos ?: 'Pos Piket Utama & Gerbang',
            'keterangan'              => $request->keterangan ?: 'Petugas Piket & Presensi Kehadiran Guru',
            'is_aktif'                => true,
            'ditugaskan_oleh_user_id' => Auth::id(),
        ]);

        $user = User::find($request->user_id);
        return back()->with('success', "Penugasan Piket hari {$request->hari} berhasil ditetapkan untuk {$user->name} ({$user->role})!");
    }

    /**
     * Hapus / cabut penugasan piket
     */
    public function destroy(PetugasPiket $piket)
    {
        $userName = $piket->user->name ?? 'Petugas';
        $hari = $piket->hari;
        $piket->delete();

        return back()->with('success', "Penugasan Piket hari {$hari} untuk {$userName} telah dicabut.");
    }
}
