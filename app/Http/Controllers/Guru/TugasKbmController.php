<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TugasKbm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasKbmController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal'             => 'required|date',
            'deskripsi_tugas'     => 'nullable|string|required_without:file_lampiran',
            'file_lampiran'       => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip|max:10240',
        ]);

        $user = Auth::user();
        $jadwal = \App\Models\JadwalPelajaran::with('mataPelajaran')->findOrFail($request->jadwal_pelajaran_id);

        $existing = TugasKbm::where('jadwal_pelajaran_id', $request->jadwal_pelajaran_id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        $path = $existing?->file_lampiran;
        if ($request->hasFile('file_lampiran')) {
            $path = $request->file('file_lampiran')->store('tugas_lampiran', 'public');
        }

        $deskripsi = trim((string) $request->input('deskripsi_tugas', ''));
        if ($deskripsi === '') {
            $mapelNama = $jadwal->mataPelajaran?->nama ?? 'Mata Pelajaran';
            $deskripsi = "Tugas {$mapelNama}. Silakan unduh dan kerjakan file lampiran tugas yang disediakan guru.";
        }

        TugasKbm::updateOrCreate(
            [
                'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
                'tanggal'             => $request->tanggal,
            ],
            [
                'guru_user_id'        => $jadwal->guru_user_id ?: $user->id,
                'dibuat_oleh_user_id' => $user->id,
                'deskripsi_tugas'     => $deskripsi,
                'file_lampiran'       => $path,
            ]
        );

        return back()->with('success', 'Tugas / Instruksi untuk siswa berhasil disimpan dan dapat langsung dilihat siswa.');
    }

    public function lampiran(TugasKbm $tugas)
    {
        if (empty($tugas->file_lampiran) || !Storage::disk('public')->exists($tugas->file_lampiran)) {
            abort(404, 'File lampiran tugas tidak ditemukan.');
        }

        $user = Auth::user();
        $tugas->loadMissing('jadwal');

        if ($user->role === 'siswa') {
            $siswa = \App\Models\Siswa::with('kelas')->where('user_id', $user->id)->first();
            $kelasUser = $user->kelas_id ? \App\Models\Kelas::find($user->kelas_id) : null;
            $kelasCandidates = array_values(array_unique(array_filter([
                $siswa?->kelas?->nama_kelas,
                $siswa?->kelas?->nama,
                $kelasUser?->nama_kelas,
                $kelasUser?->nama,
            ])));
            if (!empty($kelasCandidates) && $tugas->jadwal && !in_array($tugas->jadwal->kelas, $kelasCandidates, true)) {
                abort(403, 'Lampiran tugas ini bukan untuk kelas Anda.');
            }
        }

        $fullPath = Storage::disk('public')->path($tugas->file_lampiran);
        return response()->file($fullPath);
    }
}

