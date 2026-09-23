<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPelajaran;
use App\Models\Kurikulum;
use App\Models\MataPelajaran;
use App\Models\PerangkatAjar;
use App\Models\ProgramKerjaTugasTambahan;
use App\Models\PengaturanSekolah;

class PenugasanGuruController extends Controller
{
    /**
     * Tampilkan halaman Informasi Penugasan Guru (Penugasan Mengajar & Penugasan Tambahan)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'mengajar'); // mengajar / tambahan
        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2026/2027');

        // 1. Data Penugasan Mengajar (Plotting Kurikulum & Jadwal oleh Admin)
        // Ambil dari Kurikulum yang diplot ke guru ini
        $kurikulumPlot = Kurikulum::with('mataPelajaran')
            ->where('guru_user_id', $user->id)
            ->where('is_aktif', true)
            ->get();

        // Ambil juga jadwal mengajar riil guru ini
        $jadwalList = JadwalPelajaran::with('mataPelajaran')
            ->where('guru_user_id', $user->id)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        // Gabungkan mata pelajaran unik yang diampu beserta kelasnya
        $penugasanMengajar = collect();

        // Dari kurikulum
        foreach ($kurikulumPlot as $k) {
            $key = $k->mata_pelajaran_id . '_' . $k->kelas;
            $jadwalsMapel = $jadwalList->where('mata_pelajaran_id', $k->mata_pelajaran_id)->where('kelas', $k->kelas);
            $totalJpTerjadwal = $jadwalsMapel->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

            // Cek apakah sudah ada perangkat ajar yang diinput
            $perangkat = PerangkatAjar::where('guru_user_id', $user->id)
                ->where('mata_pelajaran_id', $k->mata_pelajaran_id)
                ->where('kelas', $k->kelas)
                ->latest()
                ->first();

            $penugasanMengajar->put($key, [
                'mata_pelajaran_id' => $k->mata_pelajaran_id,
                'mapel_nama'        => $k->mataPelajaran->nama ?? 'Mata Pelajaran',
                'mapel_kode'        => $k->mataPelajaran->kode ?? '',
                'kelas'             => $k->kelas,
                'alokasi_jam'       => $k->alokasi_jam,
                'terjadwal_jp'      => $totalJpTerjadwal ?: $k->terjadwal_jp,
                'sisa_jp'           => max(0, $k->alokasi_jam - ($totalJpTerjadwal ?: $k->terjadwal_jp)),
                'ruang'             => $jadwalsMapel->pluck('ruang')->filter()->unique()->implode(', ') ?: 'Ruang Kelas',
                'hari_mengajar'     => $jadwalsMapel->pluck('hari')->unique()->implode(', ') ?: '-',
                'perangkat'         => $perangkat,
            ]);
        }

        // Tambahkan jika ada jadwal mengajar yang belum masuk kurikulum
        foreach ($jadwalList as $j) {
            $key = $j->mata_pelajaran_id . '_' . $j->kelas;
            if (!$penugasanMengajar->has($key)) {
                $perangkat = PerangkatAjar::where('guru_user_id', $user->id)
                    ->where('mata_pelajaran_id', $j->mata_pelajaran_id)
                    ->where('kelas', $j->kelas)
                    ->latest()
                    ->first();

                $jadwalsMapel = $jadwalList->where('mata_pelajaran_id', $j->mata_pelajaran_id)->where('kelas', $j->kelas);
                $totalJp = $jadwalsMapel->sum(fn($jm) => max(1, ($jm->jam_ke_selesai - $jm->jam_ke_mulai + 1)));

                $penugasanMengajar->put($key, [
                    'mata_pelajaran_id' => $j->mata_pelajaran_id,
                    'mapel_nama'        => $j->mataPelajaran->nama ?? 'Mata Pelajaran',
                    'mapel_kode'        => $j->mataPelajaran->kode ?? '',
                    'kelas'             => $j->kelas,
                    'alokasi_jam'       => $totalJp,
                    'terjadwal_jp'      => $totalJp,
                    'sisa_jp'           => 0,
                    'ruang'             => $jadwalsMapel->pluck('ruang')->filter()->unique()->implode(', ') ?: 'Ruang Kelas',
                    'hari_mengajar'     => $jadwalsMapel->pluck('hari')->unique()->implode(', ') ?: '-',
                    'perangkat'         => $perangkat,
                ]);
            }
        }

        // 2. Data Penugasan Tambahan
        $listTugasTambahan = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, $listTugasTambahan)) {
            $listTugasTambahan = array_merge([$user->jabatan_utama], $listTugasTambahan);
        }
        $listTugasTambahan = array_values(array_unique(array_filter($listTugasTambahan)));

        // Ambil program kerja yang sudah dibuat guru untuk tugas tambahan ini
        $programKerjaList = ProgramKerjaTugasTambahan::where('guru_user_id', $user->id)
            ->withCount('realisasis')
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Papan Monitoring Kehadiran Guru Real-Time Seluruh Kelas
        $todayDate = date('Y-m-d');
        $monitoringPayload = app(\App\Services\MonitoringKelasService::class)->getMonitoringKelasData(
            $request->input('hari'),
            $request->input('jam'),
            $request->input('tanggal')
        );

        $monitoringKelas = $monitoringPayload['monitoringKelas'];
        $rekapMonitoring = $monitoringPayload['rekap'];
        $hariPantau      = $monitoringPayload['hariPantau'];
        $jamPantau       = $monitoringPayload['jamPantau'];
        $isSimulasi      = $monitoringPayload['isSimulasi'];
        $periodInfo      = $monitoringPayload['periodInfo'];

        return view('guru.penugasan.index', compact(
            'penugasanMengajar',
            'listTugasTambahan',
            'programKerjaList',
            'tab',
            'tahunAjaran',
            'todayDate',
            'monitoringKelas',
            'rekapMonitoring',
            'hariPantau',
            'jamPantau',
            'isSimulasi',
            'periodInfo'
        ));
    }

    /**
     * Simpan Rencana Tugas dan Program Kerja untuk Penugasan Tambahan
     */
    public function storeProgramKerja(Request $request)
    {
        $request->validate([
            'tugas_tambahan'        => 'required|string|max:150',
            'nama_program'          => 'required|string|max:255',
            'tahun_ajaran'          => 'required|string|max:50',
            'semester'              => 'required|string|max:50',
            'tujuan'                => 'nullable|string',
            'target_waktu'          => 'nullable|string|max:100',
            'indikator_keberhasilan'=> 'nullable|string',
            'anggaran'              => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();

        ProgramKerjaTugasTambahan::create([
            'guru_user_id'          => $user->id,
            'tugas_tambahan'        => $request->tugas_tambahan,
            'tahun_ajaran'          => $request->tahun_ajaran,
            'semester'              => $request->semester,
            'nama_program'          => $request->nama_program,
            'tujuan'                => $request->tujuan,
            'target_waktu'          => $request->target_waktu,
            'indikator_keberhasilan'=> $request->indikator_keberhasilan,
            'anggaran'              => $request->anggaran ?: 0,
            'status'                => 'terencana',
        ]);

        return redirect()->route('guru.penugasan.index', ['tab' => 'tambahan'])
            ->with('success', 'Rencana Tugas dan Program Kerja untuk "' . $request->tugas_tambahan . '" berhasil disimpan!');
    }

    /**
     * Update status atau hapus program kerja
     */
    public function destroyProgramKerja(ProgramKerjaTugasTambahan $programKerja)
    {
        $user = Auth::user();
        if ($programKerja->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $programKerja->delete();

        return redirect()->route('guru.penugasan.index', ['tab' => 'tambahan'])
            ->with('success', 'Rencana program kerja berhasil dihapus.');
    }
}
