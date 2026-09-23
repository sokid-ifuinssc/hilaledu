<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PerangkatAjar;
use App\Models\MataPelajaran;
use App\Models\Kelas;

class PerangkatAjarController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isWaka = $user->isSuperAdmin() || $user->isWakaKurikulum();

        $query = PerangkatAjar::with(['guru', 'mataPelajaran', 'verifier'])
            ->latest();

        if (!$isWaka) {
            $query->where('guru_user_id', $user->id);
        }

        $perangkatList = $query->paginate(15);
        $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();

        return view('guru.perangkat_ajar.index', compact('perangkatList', 'isWaka', 'mapels', 'kelasList'));
    }

    public function create()
    {
        $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();

        return view('guru.perangkat_ajar.create', compact('mapels', 'kelasList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas'             => 'required|string',
            'tahun_ajaran'      => 'required|string',
            'semester'          => 'required|in:ganjil,genap',
            'judul'             => 'required|string|max:200',
            'link_gdrive'       => 'required|url|max:500',
            'kelengkapan'       => 'nullable|array',
        ]);

        $user = Auth::user();

        PerangkatAjar::create([
            'guru_user_id'       => $user->id,
            'mata_pelajaran_id'  => $request->mata_pelajaran_id,
            'kelas'              => $request->kelas,
            'tahun_ajaran'       => $request->tahun_ajaran,
            'semester'           => $request->semester,
            'judul'              => $request->judul,
            'link_gdrive'        => $request->link_gdrive,
            'kelengkapan_berkas' => $request->kelengkapan ?? [],
            'status'             => 'menunggu_review',
        ]);

        return redirect()->route('guru.perangkat-ajar.index')
            ->with('success', 'Link Google Drive Perangkat Pembelajaran berhasil dikirim dan menunggu verifikasi Waka Kurikulum!');
    }

    /**
     * Fitur Waka Kurikulum: Verifikasi dan Berikan Catatan Perangkat Ajar
     */
    public function verify(Request $request, PerangkatAjar $perangkatAjar)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isWakaKurikulum()) {
            abort(403, 'Hanya Waka Kurikulum atau Administrator yang berwenang memverifikasi perangkat ajar.');
        }

        $request->validate([
            'status'       => 'required|in:disetujui,perlu_perbaikan,menunggu_review',
            'catatan_waka' => 'nullable|string',
        ]);

        $perangkatAjar->update([
            'status'              => $request->status,
            'catatan_waka'        => $request->catatan_waka,
            'verified_by_user_id' => $user->id,
            'verified_at'         => now(),
        ]);

        return back()->with('success', "Status perangkat ajar berhasil diperbarui menjadi: {$perangkatAjar->status_label}.");
    }

    public function destroy(PerangkatAjar $perangkatAjar)
    {
        $user = Auth::user();
        if ($perangkatAjar->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $perangkatAjar->delete();
        return redirect()->route('guru.perangkat-ajar.index')->with('success', 'Perangkat ajar berhasil dihapus.');
    }
}
