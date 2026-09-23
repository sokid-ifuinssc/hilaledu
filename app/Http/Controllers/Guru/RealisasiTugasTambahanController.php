<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RealisasiTugasTambahan;
use App\Models\ProgramKerjaTugasTambahan;
use App\Models\PengaturanSekolah;

class RealisasiTugasTambahanController extends Controller
{
    /**
     * Tampilkan Jurnal & Laporan Realisasi Kerja Tugas Tambahan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tugasFilter = $request->query('tugas');
        $bulanFilter = $request->query('bulan');

        $query = RealisasiTugasTambahan::with(['guru', 'programKerja'])
            ->orderBy('tanggal_pelaksanaan', 'desc');

        if (!$user->isSuperAdmin() && !$user->isKepalaSekolah()) {
            $query->where('guru_user_id', $user->id);
        } else {
            if ($request->filled('guru_id')) {
                $query->where('guru_user_id', $request->guru_id);
            }
        }

        if ($tugasFilter) {
            $query->where('tugas_tambahan', $tugasFilter);
        }
        if ($bulanFilter) {
            $query->where('tanggal_pelaksanaan', 'like', "{$bulanFilter}%");
        }

        $realisasis = $query->paginate(15)->withQueryString();

        // Daftar tugas tambahan yang relevan untuk filter
        $listTugas = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, $listTugas)) {
            $listTugas = array_merge([$user->jabatan_utama], $listTugas);
        }

        return view('guru.realisasi_tugas_tambahan.index', compact(
            'realisasis',
            'tugasFilter',
            'bulanFilter',
            'listTugas'
        ));
    }

    /**
     * Form Input Realisasi Kerja Tugas Tambahan Baru
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $listTugas = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, $listTugas)) {
            $listTugas = array_merge([$user->jabatan_utama], $listTugas);
        }

        $selectedTugas = $request->query('tugas', $listTugas[0] ?? 'Tugas Tambahan');
        $programKerjaList = ProgramKerjaTugasTambahan::where('guru_user_id', $user->id)->get();

        return view('guru.realisasi_tugas_tambahan.create', compact(
            'listTugas',
            'selectedTugas',
            'programKerjaList'
        ));
    }

    /**
     * Simpan Realisasi Kerja Tugas Tambahan
     */
    public function store(Request $request)
    {
        $request->validate([
            'tugas_tambahan'      => 'required|string|max:150',
            'program_kerja_id'    => 'nullable|exists:program_kerja_tugas_tambahans,id',
            'tanggal_pelaksanaan' => 'required|date',
            'judul_kegiatan'      => 'required|string|max:255',
            'uraian_kegiatan'     => 'required|string',
            'hasil_capaian'       => 'required|string',
            'kendala_solusi'      => 'nullable|string',
            'foto'                => 'nullable|image|max:4096',
            'dokumen'             => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        $user = Auth::user();

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('realisasi_tugas_foto', 'public');
        }

        $dokumenPath = null;
        if ($request->hasFile('dokumen')) {
            $dokumenPath = $request->file('dokumen')->store('realisasi_tugas_dokumen', 'public');
        }

        RealisasiTugasTambahan::create([
            'guru_user_id'        => $user->id,
            'program_kerja_id'    => $request->program_kerja_id ?: null,
            'tugas_tambahan'      => $request->tugas_tambahan,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'judul_kegiatan'      => $request->judul_kegiatan,
            'uraian_kegiatan'     => $request->uraian_kegiatan,
            'hasil_capaian'       => $request->hasil_capaian,
            'kendala_solusi'      => $request->kendala_solusi,
            'foto_dokumentasi'    => $fotoPath,
            'dokumen_pendukung'   => $dokumenPath,
            'status_validasi'     => 'disetujui_kepsek',
        ]);

        // Update status program kerja jika dipilih
        if ($request->program_kerja_id) {
            ProgramKerjaTugasTambahan::where('id', $request->program_kerja_id)
                ->where('status', 'terencana')
                ->update(['status' => 'sedang_berjalan']);
        }

        return redirect()->route('guru.realisasi-tugas-tambahan.index')
            ->with('success', 'Laporan Realisasi Kerja Tugas Tambahan berhasil disimpan dan dicatat!');
    }

    /**
     * Detail Realisasi Kerja
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
     * Cetak Lembar Laporan Realisasi Kerja Tugas Tambahan (PDF)
     */
    public function print(Request $request)
    {
        $user = Auth::user();
        $tugas = $request->query('tugas');
        $bulan = $request->query('bulan', date('Y-m'));

        $query = RealisasiTugasTambahan::with(['guru', 'programKerja'])
            ->where('guru_user_id', $user->id)
            ->orderBy('tanggal_pelaksanaan', 'asc');

        if ($tugas) {
            $query->where('tugas_tambahan', $tugas);
        }
        if ($bulan) {
            $query->where('tanggal_pelaksanaan', 'like', "{$bulan}%");
        }

        $realisasis = $query->get();

        $settings = [
            'nama_sekolah'        => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal Arjawinangun'),
            'nama_kepala_sekolah' => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepala_sekolah'  => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'titimangsa'          => PengaturanSekolah::get('titimangsa', 'Arjawinangun, ' . date('d F Y')),
        ];

        return view('guru.realisasi_tugas_tambahan.print', compact('realisasis', 'user', 'tugas', 'bulan', 'settings'));
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

        $realisasi->delete();

        return redirect()->route('guru.realisasi-tugas-tambahan.index')
            ->with('success', 'Laporan realisasi tugas tambahan berhasil dihapus.');
    }
}
