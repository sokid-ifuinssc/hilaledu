<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RealisasiTugasTambahan;
use App\Models\ProgramKerjaTugasTambahan;
use App\Models\PengaturanSekolah;
use App\Models\User;

class RealisasiTugasTambahanController extends Controller
{
    /**
     * Tampilkan Matriks Realisasi Kerja & Evaluasi Kesesuaian Rencana Program Tugas Tambahan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tugasFilter = $request->query('tugas');
        $statusKesesuaianFilter = $request->query('status_kesesuaian');
        $statusProgramFilter = $request->query('status_program');

        // Query Rencana Program Kerja sebagai basis evaluasi kesesuaian realisasi
        $pkQuery = ProgramKerjaTugasTambahan::with(['guru', 'realisasi', 'realisasis'])
            ->orderBy('created_at', 'desc');

        if (!$user->isSuperAdmin() && !$user->isKepalaSekolah()) {
            $pkQuery->where('guru_user_id', $user->id);
        } else {
            if ($request->filled('guru_id')) {
                $pkQuery->where('guru_user_id', $request->guru_id);
            }
        }

        if ($tugasFilter) {
            $pkQuery->where('tugas_tambahan', $tugasFilter);
        }
        if ($statusProgramFilter) {
            $pkQuery->where('status', $statusProgramFilter);
        }

        // Ambil semua program kerja untuk rekap statistik
        $allPrograms = (clone $pkQuery)->get();
        $totalProgram = $allPrograms->count();
        $totalTerealisasi = $allPrograms->filter(fn($p) => $p->realisasis->isNotEmpty())->count();
        $totalSesuai = $allPrograms->filter(fn($p) => $p->realisasi && $p->realisasi->status_kesesuaian === 'sesuai')->count();
        $totalSebagian = $allPrograms->filter(fn($p) => $p->realisasi && $p->realisasi->status_kesesuaian === 'sebagian_sesuai')->count();
        $totalTidakSesuai = $allPrograms->filter(fn($p) => $p->realisasi && $p->realisasi->status_kesesuaian === 'tidak_sesuai')->count();
        $totalBelum = $totalProgram - $totalTerealisasi;

        $persentaseList = $allPrograms->map(fn($p) => $p->realisasi?->persentase_kesesuaian)->filter(fn($val) => !is_null($val));
        $avgKesesuaian = $persentaseList->count() > 0 ? round($persentaseList->avg(), 1) : 0;

        // Terapkan filter status kesesuaian jika ada
        if ($statusKesesuaianFilter) {
            if ($statusKesesuaianFilter === 'belum_realisasi') {
                $pkQuery->doesntHave('realisasis');
            } else {
                $pkQuery->whereHas('realisasis', function ($q) use ($statusKesesuaianFilter) {
                    $q->where('status_kesesuaian', $statusKesesuaianFilter);
                });
            }
        }

        $programKerjas = $pkQuery->paginate(15)->withQueryString();

        // Daftar tugas tambahan yang relevan untuk filter
        $listTugas = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, $listTugas)) {
            $listTugas = array_merge([$user->jabatan_utama], $listTugas);
        }
        $listTugas = array_values(array_unique(array_filter($listTugas)));

        $gurus = ($user->isSuperAdmin() || $user->isKepalaSekolah())
            ? User::whereIn('role', ['guru', 'kepala_sekolah'])->orderBy('name')->get()
            : collect();

        return view('guru.realisasi_tugas_tambahan.index', compact(
            'programKerjas',
            'tugasFilter',
            'statusKesesuaianFilter',
            'statusProgramFilter',
            'listTugas',
            'totalProgram',
            'totalTerealisasi',
            'totalSesuai',
            'totalSebagian',
            'totalTidakSesuai',
            'totalBelum',
            'avgKesesuaian',
            'gurus'
        ));
    }

    /**
     * Form Input Atribut Kesesuaian Realisasi Kerja Berdasarkan Rencana Program yang Telah Dibuat
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $listTugas = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, $listTugas)) {
            $listTugas = array_merge([$user->jabatan_utama], $listTugas);
        }
        $listTugas = array_values(array_unique(array_filter($listTugas)));

        // Seluruh program kerja yang sudah disusun guru
        $programKerjaList = ProgramKerjaTugasTambahan::with('realisasi')
            ->where('guru_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedProgramId = $request->query('program_id');
        $selectedProgram = null;

        if ($selectedProgramId) {
            $selectedProgram = $programKerjaList->firstWhere('id', (int) $selectedProgramId);
        }

        if (!$selectedProgram && $request->filled('tugas')) {
            $selectedProgram = $programKerjaList->where('tugas_tambahan', $request->query('tugas'))->first();
        }

        if (!$selectedProgram && $programKerjaList->isNotEmpty()) {
            $selectedProgram = $programKerjaList->first();
        }

        $selectedTugas = $selectedProgram ? $selectedProgram->tugas_tambahan : ($request->query('tugas', $listTugas[0] ?? 'Tugas Tambahan'));

        return view('guru.realisasi_tugas_tambahan.create', compact(
            'listTugas',
            'selectedTugas',
            'programKerjaList',
            'selectedProgram'
        ));
    }

    /**
     * Simpan Atribut Kesesuaian Realisasi Kerja Tugas Tambahan
     */
    public function store(Request $request)
    {
        $request->validate([
            'program_kerja_id'      => 'required|exists:program_kerja_tugas_tambahans,id',
            'tugas_tambahan'        => 'required|string|max:150',
            'tanggal_pelaksanaan'   => 'required|date',
            'status_kesesuaian'     => 'required|in:sesuai,sebagian_sesuai,tidak_sesuai',
            'persentase_kesesuaian' => 'required|integer|min:0|max:100',
            'keterangan_kesesuaian' => 'nullable|string',
            'judul_kegiatan'        => 'nullable|string|max:255',
            'uraian_kegiatan'       => 'required|string',
            'hasil_capaian'         => 'required|string',
            'realisasi_anggaran'    => 'nullable|numeric|min:0',
            'kendala_solusi'        => 'nullable|string',
            'foto'                  => 'nullable|image|max:4096',
            'dokumen'               => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'status_program'        => 'nullable|in:terencana,sedang_berjalan,tercapai,tertunda',
        ]);

        $user = Auth::user();
        $programKerja = ProgramKerjaTugasTambahan::findOrFail($request->program_kerja_id);

        if ($programKerja->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('realisasi_tugas_foto', 'public');
        }

        $dokumenPath = null;
        if ($request->hasFile('dokumen')) {
            $dokumenPath = $request->file('dokumen')->store('realisasi_tugas_dokumen', 'public');
        }

        $judulKegiatan = $request->judul_kegiatan ?: $programKerja->nama_program;

        RealisasiTugasTambahan::create([
            'guru_user_id'          => $user->id,
            'program_kerja_id'      => $programKerja->id,
            'status_kesesuaian'     => $request->status_kesesuaian,
            'persentase_kesesuaian' => $request->persentase_kesesuaian,
            'keterangan_kesesuaian' => $request->keterangan_kesesuaian,
            'tugas_tambahan'        => $request->tugas_tambahan ?: $programKerja->tugas_tambahan,
            'tanggal_pelaksanaan'   => $request->tanggal_pelaksanaan,
            'judul_kegiatan'        => $judulKegiatan,
            'uraian_kegiatan'       => $request->uraian_kegiatan,
            'hasil_capaian'         => $request->hasil_capaian,
            'realisasi_anggaran'    => $request->realisasi_anggaran ?? 0,
            'kendala_solusi'        => $request->kendala_solusi,
            'foto_dokumentasi'      => $fotoPath,
            'dokumen_pendukung'     => $dokumenPath,
            'status_validasi'       => 'disetujui_kepsek',
        ]);

        // Perbarui status ketercapaian pada rencana program kerja
        $newStatus = $request->status_program;
        if (!$newStatus) {
            $newStatus = ($request->status_kesesuaian === 'sesuai' && $request->persentase_kesesuaian >= 100)
                ? 'tercapai'
                : 'sedang_berjalan';
        }
        $programKerja->update(['status' => $newStatus]);

        return redirect()->route('guru.realisasi-tugas-tambahan.index')
            ->with('success', 'Atribut kesesuaian realisasi untuk rencana "' . $programKerja->nama_program . '" berhasil dicatat!');
    }

    /**
     * Form Edit / Pembaruan Atribut Kesesuaian Realisasi
     */
    public function edit(RealisasiTugasTambahan $realisasi)
    {
        $user = Auth::user();
        if ($realisasi->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $realisasi->load(['programKerja', 'guru']);
        $programKerja = $realisasi->programKerja;

        $listTugas = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, $listTugas)) {
            $listTugas = array_merge([$user->jabatan_utama], $listTugas);
        }
        $listTugas = array_values(array_unique(array_filter($listTugas)));

        return view('guru.realisasi_tugas_tambahan.edit', compact(
            'realisasi',
            'programKerja',
            'listTugas'
        ));
    }

    /**
     * Simpan Pembaruan Atribut Kesesuaian Realisasi
     */
    public function update(Request $request, RealisasiTugasTambahan $realisasi)
    {
        $user = Auth::user();
        if ($realisasi->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'status_kesesuaian'     => 'required|in:sesuai,sebagian_sesuai,tidak_sesuai',
            'persentase_kesesuaian' => 'required|integer|min:0|max:100',
            'keterangan_kesesuaian' => 'nullable|string',
            'tanggal_pelaksanaan'   => 'required|date',
            'uraian_kegiatan'       => 'required|string',
            'hasil_capaian'         => 'required|string',
            'realisasi_anggaran'    => 'nullable|numeric|min:0',
            'kendala_solusi'        => 'nullable|string',
            'foto'                  => 'nullable|image|max:4096',
            'dokumen'               => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'status_program'        => 'nullable|in:terencana,sedang_berjalan,tercapai,tertunda',
        ]);

        $fotoPath = $realisasi->foto_dokumentasi;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('realisasi_tugas_foto', 'public');
        }

        $dokumenPath = $realisasi->dokumen_pendukung;
        if ($request->hasFile('dokumen')) {
            $dokumenPath = $request->file('dokumen')->store('realisasi_tugas_dokumen', 'public');
        }

        $realisasi->update([
            'status_kesesuaian'     => $request->status_kesesuaian,
            'persentase_kesesuaian' => $request->persentase_kesesuaian,
            'keterangan_kesesuaian' => $request->keterangan_kesesuaian,
            'tanggal_pelaksanaan'   => $request->tanggal_pelaksanaan,
            'uraian_kegiatan'       => $request->uraian_kegiatan,
            'hasil_capaian'         => $request->hasil_capaian,
            'realisasi_anggaran'    => $request->realisasi_anggaran ?? 0,
            'kendala_solusi'        => $request->kendala_solusi,
            'foto_dokumentasi'      => $fotoPath,
            'dokumen_pendukung'     => $dokumenPath,
        ]);

        if ($realisasi->program_kerja_id) {
            $newStatus = $request->status_program;
            if (!$newStatus) {
                $newStatus = ($request->status_kesesuaian === 'sesuai' && $request->persentase_kesesuaian >= 100)
                    ? 'tercapai'
                    : 'sedang_berjalan';
            }
            ProgramKerjaTugasTambahan::where('id', $realisasi->program_kerja_id)
                ->update(['status' => $newStatus]);
        }

        return redirect()->route('guru.realisasi-tugas-tambahan.index')
            ->with('success', 'Pembaruan atribut kesesuaian realisasi berhasil disimpan!');
    }

    /**
     * Detail Realisasi Kerja & Evaluasi Kesesuaian
     */
    public function show(RealisasiTugasTambahan $realisasi)
    {
        $user = Auth::user();
        if ($realisasi->guru_user_id !== $user->id && !$user->isSuperAdmin() && !$user->isKepalaSekolah()) {
            abort(403);
        }

        $realisasi->load(['guru', 'programKerja']);
        return view('guru.realisasi_tugas_tambahan.show', compact('realisasi'));
    }

    /**
     * Cetak Lembar Laporan Realisasi & Kesesuaian Kerja Tugas Tambahan (PDF)
     */
    public function print(Request $request)
    {
        $user = Auth::user();
        $tugas = $request->query('tugas');
        $bulan = $request->query('bulan');

        $query = ProgramKerjaTugasTambahan::with(['guru', 'realisasi', 'realisasis'])
            ->where('guru_user_id', $user->id)
            ->orderBy('tugas_tambahan', 'asc')
            ->orderBy('created_at', 'asc');

        if ($tugas) {
            $query->where('tugas_tambahan', $tugas);
        }

        $programKerjas = $query->get();

        $settings = [
            'nama_sekolah'        => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal Arjawinangun'),
            'nama_kepala_sekolah' => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepala_sekolah'  => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'titimangsa'          => PengaturanSekolah::get('titimangsa', 'Arjawinangun, ' . date('d F Y')),
        ];

        return view('guru.realisasi_tugas_tambahan.print', compact('programKerjas', 'user', 'tugas', 'bulan', 'settings'));
    }

    /**
     * Hapus Laporan Realisasi
     */
    public function destroy(RealisasiTugasTambahan $realisasi)
    {
        $user = Auth::user();
        if ($realisasi->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $programKerjaId = $realisasi->program_kerja_id;
        $realisasi->delete();

        // Jika program kerja tidak memiliki realisasi lain, kembalikan statusnya ke 'terencana'
        if ($programKerjaId) {
            $hasOther = RealisasiTugasTambahan::where('program_kerja_id', $programKerjaId)->exists();
            if (!$hasOther) {
                ProgramKerjaTugasTambahan::where('id', $programKerjaId)->update(['status' => 'terencana']);
            }
        }

        return redirect()->route('guru.realisasi-tugas-tambahan.index')
            ->with('success', 'Catatan realisasi tugas tambahan berhasil dihapus.');
    }
}
