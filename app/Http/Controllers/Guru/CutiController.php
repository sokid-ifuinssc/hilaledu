<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CutiGuru;
use App\Models\User;

class CutiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isApprover = $user->isSuperAdmin() || $user->isKepalaSekolah() || $user->isWakaKurikulum();

        $query = CutiGuru::with(['guru', 'guruPengganti', 'approver'])->latest();

        if (!$isApprover) {
            $query->where('guru_user_id', $user->id);
        }

        $cutiList = $query->paginate(15);
        $guruList = User::where('role', 'guru')
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('guru.cuti.index', compact('cutiList', 'isApprover', 'guruList'));
    }

    public function create()
    {
        $user = Auth::user();
        $guruList = User::where('role', 'guru')
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('guru.cuti.create', compact('guruList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_cuti'        => 'required|string|max:100',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'            => 'required|string',
            'guru_pengganti_id' => 'nullable|exists:users,id',
            'lampiran'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
        ]);

        $user = Auth::user();
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('cuti_lampiran', 'public');
        }

        CutiGuru::create([
            'guru_user_id'        => $user->id,
            'guru_pengganti_id'   => $request->guru_pengganti_id,
            'jenis_cuti'          => $request->jenis_cuti,
            'tanggal_mulai'       => $request->tanggal_mulai,
            'tanggal_selesai'     => $request->tanggal_selesai,
            'alasan'              => $request->alasan,
            'lampiran_surat'      => $lampiranPath,
            'status'              => 'diajukan',
        ]);

        return redirect()->route('guru.cuti.index')
            ->with('success', 'Pengajuan cuti berhasil dikirim! Menunggu persetujuan Kepala Sekolah / Waka Kurikulum.');
    }

    /**
     * Approval cuti oleh Kepala Sekolah atau Waka Kurikulum
     */
    public function approve(Request $request, CutiGuru $cuti)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isKepalaSekolah() && !$user->isWakaKurikulum()) {
            abort(403, 'Hanya Kepala Sekolah atau Waka Kurikulum yang berhak memproses persetujuan cuti.');
        }

        $request->validate([
            'status'           => 'required|in:disetujui_kepsek,disetujui_waka,ditolak',
            'catatan_approval' => 'nullable|string',
        ]);

        $cuti->update([
            'status'              => $request->status,
            'catatan_approval'    => $request->catatan_approval,
            'approved_by_user_id' => $user->id,
        ]);

        $statusMsg = match ($request->status) {
            'disetujui_kepsek' => 'Disetujui oleh Kepala Sekolah',
            'disetujui_waka'   => 'Disetujui oleh Waka Kurikulum',
            'ditolak'          => 'Ditolak',
        };

        return back()->with('success', "Pengajuan cuti {$cuti->guru->name} telah {$statusMsg}. Selama periode cuti, jadwal mengajar otomatis dialihkan ke guru pengganti.");
    }
}
