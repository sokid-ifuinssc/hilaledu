<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KeluhanKbm;
use App\Models\SaranPerbaikan;
use App\Models\User;

class KeluhanKbmController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->query('status');

        $isExecutive = $user->isSuperAdmin() || $user->isKepalaSekolah() ||
                       $user->isWakaKurikulum() || $user->isWakaKesiswaan() ||
                       $user->isWakaSarpras() || $user->isWakaHubin() || $user->isBk();

        $query = KeluhanKbm::with(['targetGuru', 'mataPelajaran', 'saranPerbaikan.author', 'siswa'])
            ->orderBy('created_at', 'desc');

        if (!$isExecutive) {
            // Jika wali kelas, tampilkan keluhan untuk dirinya dan keluhan di kelas binaannya
            if ($user->isWaliKelas()) {
                // Cari nama kelas binaan dari tugas_tambahan misal 'Wali Kelas XI TO'
                $kelasBinaan = null;
                foreach ($user->tugas_tambahan ?? [] as $t) {
                    if (str_starts_with($t, 'Wali Kelas ')) {
                        $kelasBinaan = trim(str_replace('Wali Kelas ', '', $t));
                        break;
                    }
                }

                $query->where(function ($q) use ($user, $kelasBinaan) {
                    $q->where('target_guru_user_id', $user->id);
                    if ($kelasBinaan) {
                        $q->orWhere('kelas', 'like', "%{$kelasBinaan}%");
                    }
                });
            } else {
                $query->where('target_guru_user_id', $user->id);
            }
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $keluhans = $query->paginate(15);

        return view('guru.keluhan.index', compact('keluhans', 'isExecutive', 'statusFilter'));
    }

    public function show(KeluhanKbm $keluhan)
    {
        $user = Auth::user();
        $keluhan->load(['targetGuru', 'mataPelajaran', 'saranPerbaikan.author', 'siswa']);

        $canGiveAdvice = $user->isSuperAdmin() || $user->isKepalaSekolah() ||
                         $user->isWakaKurikulum() || $user->isWakaKesiswaan() ||
                         $user->isKaprog() || $user->isWaliKelas();

        return view('guru.keluhan.show', compact('keluhan', 'canGiveAdvice'));
    }

    /**
     * Guru menanggapi keluhan atau memperbarui catatan tindak lanjut
     */
    public function updateStatus(Request $request, KeluhanKbm $keluhan)
    {
        $user = Auth::user();
        $request->validate([
            'status'                => 'required|in:baru,diproses,selesai',
            'catatan_tindak_lanjut' => 'required|string',
        ]);

        $keluhan->update([
            'status'                => $request->status,
            'catatan_tindak_lanjut' => $request->catatan_tindak_lanjut,
        ]);

        return back()->with('success', 'Catatan tindak lanjut dan status keluhan KBM berhasil diperbarui!');
    }

    /**
     * Kaprog, Waka, Kepala Sekolah, atau Walikelas memberikan arahan / saran perbaikan untuk guru
     */
    public function storeSaran(Request $request, KeluhanKbm $keluhan)
    {
        $user = Auth::user();
        $request->validate([
            'judul_arahan'         => 'required|string|max:200',
            'arahan_pembinaan'     => 'required|string',
            'rekomendasi_tindakan' => 'nullable|string',
        ]);

        // Tentukan label role author
        $roleLabel = 'Waka / Pimpinan';
        if ($user->isKepalaSekolah()) $roleLabel = 'Kepala Sekolah';
        elseif ($user->isWakaKurikulum()) $roleLabel = 'Waka Kurikulum';
        elseif ($user->isWakaKesiswaan()) $roleLabel = 'Waka Kesiswaan';
        elseif ($user->isKaprog()) $roleLabel = 'Ketua Program Keahlian (Kaprog)';
        elseif ($user->isWaliKelas()) $roleLabel = 'Wali Kelas';

        SaranPerbaikan::create([
            'keluhan_kbm_id'       => $keluhan->id,
            'target_guru_user_id'  => $keluhan->target_guru_user_id,
            'author_user_id'       => $user->id,
            'role_author'          => $roleLabel,
            'judul_arahan'         => $request->judul_arahan,
            'arahan_pembinaan'     => $request->arahan_pembinaan,
            'rekomendasi_tindakan' => $request->rekomendasi_tindakan,
            'status'               => 'dibaca',
        ]);

        $keluhan->update(['status' => 'diproses']);

        return back()->with('success', "Arahan pembinaan dan saran perbaikan berhasil dikirimkan kepada {$keluhan->targetGuru->name}!");
    }

    /**
     * Guru menanggapi saran perbaikan dari Kepala Sekolah / Waka / Kaprog
     */
    public function tanggapiSaran(Request $request, SaranPerbaikan $saran)
    {
        $user = Auth::user();
        if ($saran->target_guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'tanggapan_guru' => 'required|string',
        ]);

        $saran->update([
            'tanggapan_guru' => $request->tanggapan_guru,
            'status'         => 'ditanggapi',
        ]);

        return back()->with('success', 'Tanggapan dan komitmen perbaikan Anda berhasil disimpan!');
    }
}
