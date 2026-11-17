<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TugasKbm;
use Illuminate\Support\Facades\Auth;

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
}

