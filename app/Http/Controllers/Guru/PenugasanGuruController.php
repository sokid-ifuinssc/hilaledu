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
use App\Models\KalenderAkademik;
use App\Models\KalenderAkademikEvent;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PenugasanGuruController extends Controller
{
    /**
     * Tampilkan halaman Informasi Penugasan Guru (Penugasan Mengajar & Penugasan Tambahan)
     */
    public function index(Request $request)
    {
        $this->ensureProgramKerjaKalenderColumns();
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

        // Tambahkan dari getMapelDiampu() jika guru belum terisi plotting jadwal/kurikulum spesifik
        if ($penugasanMengajar->isEmpty()) {
            $fallbackMapels = $user->getMapelDiampu();
            $allActiveKelas = \App\Models\Kelas::orderBy('nama_kelas')->get();

            foreach ($fallbackMapels as $m) {
                // Tentukan kelas yang sesuai dengan tingkat mapel (X, XI, XII)
                $matchedKelas = $allActiveKelas->filter(function($k) use ($m) {
                    if (empty($m->tingkat)) return true;
                    return str_starts_with(strtoupper(trim($k->nama_kelas)), strtoupper(trim($m->tingkat)));
                });

                if ($matchedKelas->isEmpty()) {
                    $matchedKelas = $allActiveKelas->take(2);
                }

                foreach ($matchedKelas as $k) {
                    $key = $m->id . '_' . $k->nama_kelas;
                    if (!$penugasanMengajar->has($key)) {
                        $perangkat = PerangkatAjar::where('guru_user_id', $user->id)
                            ->where('mata_pelajaran_id', $m->id)
                            ->where('kelas', $k->nama_kelas)
                            ->latest()
                            ->first();

                        $penugasanMengajar->put($key, [
                            'mata_pelajaran_id' => $m->id,
                            'mapel_nama'        => $m->nama,
                            'mapel_kode'        => $m->kode ?? '',
                            'kelas'             => $k->nama_kelas,
                            'alokasi_jam'       => $m->jam_per_minggu ?: 2,
                            'terjadwal_jp'      => $m->jam_per_minggu ?: 2,
                            'sisa_jp'           => 0,
                            'ruang'             => 'Ruang Kelas ' . $k->nama_kelas,
                            'hari_mengajar'     => 'Penugasan Mengajar',
                            'perangkat'         => $perangkat,
                        ]);
                    }
                }
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

        // 4. Data Eskul, Kelas & Siswa untuk modal input peserta eskul (Kesiswaan, Pembina OSIS, Pembina Eskul)
        $allEskuls = \App\Models\Ekstrakurikuler::where('is_aktif', true)->orderBy('nama')->get();
        $allKelases = \App\Models\Kelas::where('is_aktif', true)->orderBy('nama_kelas')->get();
        $allSiswas = \App\Models\User::where('role', 'siswa')
            ->where('is_active', true)
            ->with('kelas')
            ->orderBy('name')
            ->get();

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
            'periodInfo',
            'allEskuls',
            'allKelases',
            'allSiswas'
        ));
    }

    /**
     * Halaman Khusus Rencana Program Kerja Tugas Tambahan
     */
    public function rencanaProgramKerja(Request $request)
    {
        $this->ensureProgramKerjaKalenderColumns();
        $user = Auth::user();
        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2026/2027');
        $semesterAktif = PengaturanSekolah::get('semester', 'Ganjil');

        $listTugasTambahan = $user->tugas_tambahan ?? [];
        if (!empty($user->jabatan_utama) && !in_array($user->jabatan_utama, array_merge(['Guru', 'Guru Pengajar', 'Siswa', 'Tendik', 'Tenaga Kependidikan'], $listTugasTambahan))) {
            $listTugasTambahan = array_merge([$user->jabatan_utama], $listTugasTambahan);
        }
        $listTugasTambahan = array_values(array_unique(array_filter($listTugasTambahan)));

        $tugasFilter = $request->query('tugas');
        $semesterFilter = $request->query('semester');
        $statusFilter = $request->query('status');

        $baseQuery = ProgramKerjaTugasTambahan::with(['guru', 'realisasi'])
            ->withCount('realisasis')
            ->orderBy('created_at', 'desc');

        if (!$user->isSuperAdmin() && !$user->isKepalaSekolah()) {
            $baseQuery->where('guru_user_id', $user->id);
        } elseif ($request->filled('guru_id')) {
            $baseQuery->where('guru_user_id', $request->guru_id);
        }

        $allPrograms = (clone $baseQuery)->get();
        $totalProgram = $allPrograms->count();
        $totalTerencana = $allPrograms->where('status', 'terencana')->count();
        $totalBerjalan = $allPrograms->where('status', 'sedang_berjalan')->count();
        $totalTercapai = $allPrograms->where('status', 'tercapai')->count();
        $totalTertunda = $allPrograms->where('status', 'tertunda')->count();

        if ($tugasFilter) {
            $baseQuery->where('tugas_tambahan', $tugasFilter);
        }
        if ($semesterFilter) {
            $baseQuery->where('semester', $semesterFilter);
        }
        if ($statusFilter) {
            $baseQuery->where('status', $statusFilter);
        }

        $programKerjaList = $baseQuery->get();

        $gurus = ($user->isSuperAdmin() || $user->isKepalaSekolah())
            ? \App\Models\User::whereIn('role', ['guru', 'tendik', 'kepala_sekolah'])->orderBy('name')->get()
            : collect();

        return view('guru.program_kerja_tambahan.index', compact(
            'listTugasTambahan',
            'programKerjaList',
            'allPrograms',
            'tahunAjaran',
            'semesterAktif',
            'tugasFilter',
            'semesterFilter',
            'statusFilter',
            'totalProgram',
            'totalTerencana',
            'totalBerjalan',
            'totalTercapai',
            'totalTertunda',
            'gurus'
        ));
    }

    /**
     * Simpan Rencana Tugas dan Program Kerja untuk Penugasan Tambahan
     */
    public function storeProgramKerja(Request $request)
    {
        $this->ensureProgramKerjaKalenderColumns();

        $request->validate([
            'tugas_tambahan'        => 'required|string|max:150',
            'nama_program'          => 'required|string|max:255',
            'tahun_ajaran'          => 'required|string|max:50',
            'semester'              => 'required|string|max:50',
            'tujuan'                => 'nullable|string',
            'target_waktu'          => 'nullable|string|max:100',
            'tanggal_mulai'         => 'nullable|date',
            'tanggal_selesai'       => 'nullable|date|after_or_equal:tanggal_mulai',
            'indikator_keberhasilan'=> 'nullable|string',
            'anggaran'              => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $tglMulai = $request->input('tanggal_mulai') ?: null;
        $tglSelesai = $tglMulai ? ($request->input('tanggal_selesai') ?: $tglMulai) : null;
        $targetWaktu = trim((string) $request->input('target_waktu', ''));

        if ($targetWaktu === '' && $tglMulai) {
            $start = Carbon::parse($tglMulai);
            $end = Carbon::parse($tglSelesai);
            $targetWaktu = $start->isSameDay($end)
                ? $start->translatedFormat('d M Y')
                : $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
        }

        $programKerja = ProgramKerjaTugasTambahan::create([
            'guru_user_id'          => $user->id,
            'tugas_tambahan'        => $request->tugas_tambahan,
            'tahun_ajaran'          => $request->tahun_ajaran,
            'semester'              => $request->semester,
            'nama_program'          => $request->nama_program,
            'tujuan'                => $request->tujuan,
            'target_waktu'          => $targetWaktu ?: null,
            'tanggal_mulai'         => $tglMulai,
            'tanggal_selesai'       => $tglSelesai,
            'indikator_keberhasilan'=> $request->indikator_keberhasilan,
            'anggaran'              => $request->anggaran ?: 0,
            'status'                => 'terencana',
        ]);

        $synced = $this->syncProgramKerjaToKalender($programKerja, $user);
        $msg = 'Rencana Tugas dan Program Kerja untuk "' . $request->tugas_tambahan . '" berhasil disimpan'
            . ($synced ? ' dan otomatis ditampilkan di Kalender Akademik!' : '!');

        if ($request->input('redirect_to') === 'rencana') {
            return redirect()->route('guru.program-kerja-tambahan.index')->with('success', $msg);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Perbarui Rencana Program Kerja Tugas Tambahan
     */
    public function updateProgramKerja(Request $request, ProgramKerjaTugasTambahan $programKerja)
    {
        $this->ensureProgramKerjaKalenderColumns();

        $user = Auth::user();
        if ($programKerja->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'tugas_tambahan'        => 'required|string|max:150',
            'nama_program'          => 'required|string|max:255',
            'tahun_ajaran'          => 'required|string|max:50',
            'semester'              => 'required|string|max:50',
            'tujuan'                => 'nullable|string',
            'target_waktu'          => 'nullable|string|max:100',
            'tanggal_mulai'         => 'nullable|date',
            'tanggal_selesai'       => 'nullable|date|after_or_equal:tanggal_mulai',
            'indikator_keberhasilan'=> 'nullable|string',
            'anggaran'              => 'nullable|numeric|min:0',
            'status'                => 'nullable|in:terencana,sedang_berjalan,tercapai,tertunda',
        ]);

        $tglMulai = $request->input('tanggal_mulai') ?: null;
        $tglSelesai = $tglMulai ? ($request->input('tanggal_selesai') ?: $tglMulai) : null;
        $targetWaktu = trim((string) $request->input('target_waktu', ''));

        if ($targetWaktu === '' && $tglMulai) {
            $start = Carbon::parse($tglMulai);
            $end = Carbon::parse($tglSelesai);
            $targetWaktu = $start->isSameDay($end)
                ? $start->translatedFormat('d M Y')
                : $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
        }

        $programKerja->update([
            'tugas_tambahan'        => $request->tugas_tambahan,
            'tahun_ajaran'          => $request->tahun_ajaran,
            'semester'              => $request->semester,
            'nama_program'          => $request->nama_program,
            'tujuan'                => $request->tujuan,
            'target_waktu'          => $targetWaktu ?: null,
            'tanggal_mulai'         => $tglMulai,
            'tanggal_selesai'       => $tglSelesai,
            'indikator_keberhasilan'=> $request->indikator_keberhasilan,
            'anggaran'              => $request->anggaran ?: 0,
            'status'                => $request->status ?: $programKerja->status,
        ]);

        $synced = $this->syncProgramKerjaToKalender($programKerja, $programKerja->guru ?? $user);
        $msg = 'Rencana program kerja "' . $programKerja->nama_program . '" berhasil diperbarui'
            . ($synced ? ' dan disinkronkan ke Kalender Akademik.' : '.');

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Update status atau hapus program kerja
     */
    public function destroyProgramKerja(ProgramKerjaTugasTambahan $programKerja)
    {
        $this->ensureProgramKerjaKalenderColumns();

        $user = Auth::user();
        if ($programKerja->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        if (!empty($programKerja->kalender_akademik_event_id)) {
            try {
                KalenderAkademikEvent::where('id', $programKerja->kalender_akademik_event_id)->delete();
            } catch (\Throwable $e) {
            }
        }

        $programKerja->delete();

        return redirect()->back()
            ->with('success', 'Rencana program kerja dan agenda kalender terkait berhasil dihapus.');
    }

    private function ensureProgramKerjaKalenderColumns(): void
    {
        try {
            if (!Schema::hasTable('program_kerja_tugas_tambahans')) {
                return;
            }
            Schema::table('program_kerja_tugas_tambahans', function (Blueprint $table) {
                if (!Schema::hasColumn('program_kerja_tugas_tambahans', 'tanggal_mulai')) {
                    $table->date('tanggal_mulai')->nullable()->after('target_waktu');
                }
                if (!Schema::hasColumn('program_kerja_tugas_tambahans', 'tanggal_selesai')) {
                    $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
                }
                if (!Schema::hasColumn('program_kerja_tugas_tambahans', 'kalender_akademik_event_id')) {
                    $table->unsignedBigInteger('kalender_akademik_event_id')->nullable()->after('catatan');
                }
            });
        } catch (\Throwable $e) {
        }
    }

    private function syncProgramKerjaToKalender(ProgramKerjaTugasTambahan $programKerja, $user): bool
    {
        try {
            if (!Schema::hasTable('kalender_akademiks') || !Schema::hasTable('kalender_akademik_events')) {
                return false;
            }

            if (empty($programKerja->tanggal_mulai)) {
                if (!empty($programKerja->kalender_akademik_event_id)) {
                    KalenderAkademikEvent::where('id', $programKerja->kalender_akademik_event_id)->delete();
                    $programKerja->update(['kalender_akademik_event_id' => null]);
                }
                return false;
            }

            $tglMulai = $programKerja->tanggal_mulai->format('Y-m-d');
            $tglSelesai = ($programKerja->tanggal_selesai ?: $programKerja->tanggal_mulai)->format('Y-m-d');
            $m = (int) date('n', strtotime($tglMulai));
            $semester = ($m >= 7 && $m <= 12) ? '1' : '2';

            $kalender = KalenderAkademik::where('tahun_ajaran', $programKerja->tahun_ajaran)->first()
                ?: KalenderAkademik::where('is_aktif', true)->first()
                ?: KalenderAkademik::first();

            if (!$kalender) {
                $yearStart = (int) date('Y', strtotime($tglMulai));
                if ($m < 7) {
                    $yearStart--;
                }
                $taLabel = $programKerja->tahun_ajaran ?: ($yearStart . '/' . ($yearStart + 1));
                $kalender = KalenderAkademik::create([
                    'tahun_ajaran'         => $taLabel,
                    'nama_kalender'        => 'Kalender Akademik ' . $taLabel,
                    'tanggal_mulai'        => $yearStart . '-07-01',
                    'tanggal_selesai'      => ($yearStart + 1) . '-06-30',
                    'tanggal_mulai_smt1'   => $yearStart . '-07-01',
                    'tanggal_selesai_smt1' => $yearStart . '-12-31',
                    'tanggal_mulai_smt2'   => ($yearStart + 1) . '-01-01',
                    'tanggal_selesai_smt2' => ($yearStart + 1) . '-06-30',
                    'is_aktif'             => true,
                    'created_by_user_id'   => $user->id ?? auth()->id(),
                ]);
            }

            $tugasLower = strtolower((string) $programKerja->tugas_tambahan);
            $warnaBg = match (true) {
                str_contains($tugasLower, 'hubin')
                    || str_contains($tugasLower, 'humas')
                    || str_contains($tugasLower, 'prakerin')
                    || str_contains($tugasLower, 'bkk')
                    || str_contains($tugasLower, 'industri') => 'purple',
                str_contains($tugasLower, 'kesiswaan')
                    || str_contains($tugasLower, 'osis')
                    || str_contains($tugasLower, 'eskul')
                    || str_contains($tugasLower, 'ekstrakurikuler')
                    || str_contains($tugasLower, 'bk') => 'emerald',
                str_contains($tugasLower, 'kurikulum')
                    || str_contains($tugasLower, 'kaprog')
                    || str_contains($tugasLower, 'kepala program')
                    || str_contains($tugasLower, 'perpustakaan')
                    || str_contains($tugasLower, 'lab') => 'blue',
                default => 'yellow',
            };

            $judul = Str::limit('[' . $programKerja->tugas_tambahan . '] ' . $programKerja->nama_program, 250, '...');
            $pic = $user->name ?? 'Guru';
            $ketParts = ["Program Kerja {$programKerja->tugas_tambahan} | Pengusul: {$pic}"];
            if (!empty($programKerja->tujuan)) {
                $ketParts[] = 'Tujuan: ' . $programKerja->tujuan;
            }
            if (!empty($programKerja->indikator_keberhasilan)) {
                $ketParts[] = 'Output: ' . $programKerja->indikator_keberhasilan;
            }
            $keterangan = implode(' | ', $ketParts);

            $event = null;
            if (!empty($programKerja->kalender_akademik_event_id)) {
                $event = KalenderAkademikEvent::find($programKerja->kalender_akademik_event_id);
            }

            $payload = [
                'kalender_akademik_id' => $kalender->id,
                'tanggal_mulai'        => $tglMulai,
                'tanggal_selesai'      => $tglSelesai,
                'semester'             => (string) $semester,
                'judul_kegiatan'       => $judul,
                'kategori'             => 'kegiatan_sekolah',
                'warna_bg'             => $warnaBg,
                'keterangan'           => $keterangan,
                'is_libur'             => false,
                'sumber'               => 'program_kerja_tugas_tambahan',
            ];

            if ($event) {
                $event->update($payload);
            } else {
                $event = KalenderAkademikEvent::create($payload);
                $programKerja->update(['kalender_akademik_event_id' => $event->id]);
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Cetak Lampiran SK Jam Mengajar khusus untuk guru yang sedang login
     */
    public function printSkMengajar(Request $request)
    {
        $guru = Auth::user();
        $tahun = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        $guru->load(['kurikulums' => function ($q) use ($tahun, $semester) {
            $q->where('tahun_ajaran', $tahun)
              ->where('semester', $semester)
              ->with('mataPelajaran');
        }]);

        $gurus = collect([$guru]);

        return view('admin.jadwal.sk_jam_mengajar_print', compact('gurus', 'tahun', 'semester'));
    }

    /**
     * Cetak Lampiran SK Tugas Tambahan khusus untuk guru yang sedang login
     */
    public function printSkTugasTambahan(Request $request)
    {
        $guru = Auth::user();
        $tahun = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        $gurus = collect([$guru]);

        return view('admin.jadwal.sk_tugas_tambahan_print', compact('gurus', 'tahun', 'semester'));
    }
}
