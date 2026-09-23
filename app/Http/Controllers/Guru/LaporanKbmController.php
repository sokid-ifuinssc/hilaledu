<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanKbm;
use App\Models\LaporanKbmPresensi;
use App\Models\JadwalPelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;

class LaporanKbmController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tanggal = $request->query('tanggal');
        $kelas = $request->query('kelas');

        $query = LaporanKbm::with(['jadwal.mataPelajaran', 'rencana', 'guru'])
            ->orderBy('tanggal_realisasi', 'desc');

        if (!$user->isSuperAdmin() && !$user->isWakaKurikulum() && !$user->isKepalaSekolah()) {
            $query->where('guru_user_id', $user->id);
        }

        if ($tanggal) {
            $query->where('tanggal_realisasi', $tanggal);
        }
        if ($kelas) {
            $query->whereHas('jadwal', fn($q) => $q->where('kelas', $kelas));
        }

        $laporans = $query->paginate(15);
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();

        return view('guru.laporan_kbm.index', compact('laporans', 'tanggal', 'kelas', 'kelasList'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $jadwalId = $request->query('jadwal_id');
        $rencanaId = $request->query('rencana_id');

        $jadwals = JadwalPelajaran::with('mataPelajaran')
            ->where('guru_user_id', $user->id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $selectedJadwal = null;
        $students = collect();
        $rencana = null;
        $rencanaList = collect();

        if ($jadwalId) {
            $selectedJadwal = JadwalPelajaran::with('mataPelajaran')->find($jadwalId);
            if ($selectedJadwal) {
                // Ambil data siswa dari kelas ini
                $kelasNama = trim($selectedJadwal->kelas);
                $kelasObj = Kelas::where('nama_kelas', $kelasNama)->orWhere('nama', $kelasNama)->first();
                if ($kelasObj) {
                    $students = Siswa::where('kelas_id', $kelasObj->id)
                        ->where('status', 'aktif')
                        ->orderBy('nama_lengkap')
                        ->get();
                }

                // Ambil seluruh rencana pembelajaran (RPP) yang tersedia untuk jadwal ini
                $rencanaList = RencanaPembelajaran::with('tujuanPembelajaran')
                    ->where('jadwal_pelajaran_id', $jadwalId)
                    ->orderBy('pertemuan_ke')
                    ->get();

                if ($rencanaId) {
                    $rencana = $rencanaList->firstWhere('id', $rencanaId) ?: RencanaPembelajaran::with('tujuanPembelajaran')->find($rencanaId);
                } else {
                    $rencana = $rencanaList->last();
                }
            }
        } elseif ($rencanaId) {
            $rencana = RencanaPembelajaran::with(['jadwal.mataPelajaran', 'tujuanPembelajaran'])->find($rencanaId);
            if ($rencana && $rencana->jadwal) {
                $selectedJadwal = $rencana->jadwal;
                $jadwalId = $selectedJadwal->id;
                $kelasNama = trim($selectedJadwal->kelas);
                $kelasObj = Kelas::where('nama_kelas', $kelasNama)->orWhere('nama', $kelasNama)->first();
                if ($kelasObj) {
                    $students = Siswa::where('kelas_id', $kelasObj->id)
                        ->where('status', 'aktif')
                        ->orderBy('nama_lengkap')
                        ->get();
                }
                $rencanaList = RencanaPembelajaran::with('tujuanPembelajaran')
                    ->where('jadwal_pelajaran_id', $jadwalId)
                    ->orderBy('pertemuan_ke')
                    ->get();
            }
        }

        return view('guru.laporan_kbm.create', compact('jadwals', 'selectedJadwal', 'students', 'rencana', 'rencanaList', 'jadwalId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id'   => 'required|exists:jadwal_pelajarans,id',
            'tanggal_realisasi'     => 'required|date',
            'kesesuaian_rencana'    => 'required|in:sesuai,sebagian,tidak_sesuai,materi_pengganti',
            'status_pelaksanaan'    => 'required|in:sesuai_jadwal,ganti_hari,jam_tambahan,lainnya',
            'presensi'              => 'required|array',
            'foto'                  => 'nullable|image|max:3072',
        ]);

        $user = Auth::user();
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kbm_dokumentasi', 'public');
        }

        $laporan = LaporanKbm::create([
            'jadwal_pelajaran_id'   => $request->jadwal_pelajaran_id,
            'rencana_pembelajaran_id'=> $request->rencana_pembelajaran_id ?: null,
            'guru_user_id'          => $user->id,
            'tanggal_realisasi'     => $request->tanggal_realisasi,
            'kesesuaian_rencana'    => $request->kesesuaian_rencana,
            'keterangan_kesesuaian' => $request->keterangan_kesesuaian,
            'status_pelaksanaan'    => $request->status_pelaksanaan,
            'keterangan_pelaksanaan'=> $request->keterangan_pelaksanaan,
            'catatan_kegiatan'      => $request->catatan_kegiatan,
            'foto_dokumentasi'      => $fotoPath,
        ]);

        // Simpan presensi siswa
        $hadirCount = 0;
        $tidakHadirCount = 0;

        foreach ($request->presensi as $siswaId => $status) {
            $ket = $request->keterangan_siswa[$siswaId] ?? null;

            LaporanKbmPresensi::create([
                'laporan_kbm_id' => $laporan->id,
                'siswa_user_id'  => $siswaId,
                'status'         => $status,
                'keterangan'     => $ket,
            ]);

            if (in_array($status, ['hadir', 'terlambat'])) {
                $hadirCount++;
            } else {
                $tidakHadirCount++;
            }
        }

        $laporan->update([
            'jumlah_siswa_hadir'       => $hadirCount,
            'jumlah_siswa_tidak_hadir' => $tidakHadirCount,
            'jumlah_siswa_total'       => $hadirCount + $tidakHadirCount,
        ]);

        return redirect()->route('guru.laporan-kbm.show', $laporan)
            ->with('success', "Laporan KBM dan Presensi Siswa berhasil disimpan! (Hadir: {$hadirCount}, Tidak Hadir: {$tidakHadirCount})");
    }

    public function show(LaporanKbm $laporanKbm)
    {
        $laporanKbm->load(['jadwal.mataPelajaran', 'rencana', 'guru', 'presensiSiswa.siswa']);
        return view('guru.laporan_kbm.show', compact('laporanKbm'));
    }

    public function print(LaporanKbm $laporanKbm)
    {
        $laporanKbm->load(['jadwal.mataPelajaran', 'rencana', 'guru', 'presensiSiswa.siswa']);
        return view('guru.laporan_kbm.print', compact('laporanKbm'));
    }

    public function destroy(LaporanKbm $laporanKbm)
    {
        $user = Auth::user();
        if ($laporanKbm->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $laporanKbm->presensiSiswa()->delete();
        $laporanKbm->delete();

        return redirect()->route('guru.laporan-kbm.index')->with('success', 'Laporan KBM berhasil dihapus.');
    }
}
