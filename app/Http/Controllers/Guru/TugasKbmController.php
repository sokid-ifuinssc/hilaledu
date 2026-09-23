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
            'deskripsi_tugas'     => 'required|string',
            'file_lampiran'       => 'nullable|file|max:5120', // max 5MB
        ]);

        $user = Auth::user();

        $path = null;
        if ($request->hasFile('file_lampiran')) {
            $path = $request->file('file_lampiran')->store('tugas_lampiran', 'public');
        }

        $jadwal = \App\Models\JadwalPelajaran::findOrFail($request->jadwal_pelajaran_id);

        TugasKbm::updateOrCreate(
            [
                'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
                'tanggal'             => $request->tanggal,
            ],
            [
                'guru_user_id'        => $jadwal->guru_user_id,
                'dibuat_oleh_user_id' => $user->id,
                'deskripsi_tugas'     => $request->deskripsi_tugas,
                'file_lampiran'       => $path,
            ]
        );

        return back()->with('success', 'Tugas / Instruksi untuk jadwal ini berhasil disimpan.');
    }
}

