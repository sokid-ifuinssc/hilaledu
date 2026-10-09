<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\AnggotaEkstrakurikuler;
use App\Models\RencanaKegiatanEskul;
use App\Models\LaporanKegiatanEskul;
use App\Models\PresensiEskul;
use App\Models\User;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuruEkstrakurikulerController extends Controller
{
    /**
     * Pastikan user adalah pembina dari eskul terkait atau superadmin
     */
    protected function authorizePembina(Ekstrakurikuler $ekstrakurikuler): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return;
        }

        if ($ekstrakurikuler->pembina_guru_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda bukan pembina yang ditugaskan untuk ekstrakurikuler ini.');
        }
    }

    /**
     * Dashboard / daftar eskul yang dibina oleh guru yang sedang login
     */
    public function index()
    {
        $user = auth()->user();

        $eskuls = Ekstrakurikuler::query()
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('pembina_guru_id', $user->id))
            ->with(['pembina', 'ketua'])
            ->withCount([
                'anggotas as total_anggota' => fn($q) => $q->where('status', 'aktif'),
                'rencanaKegiatans as total_rencana',
                'laporanKegiatans as total_laporan',
            ])
            ->orderBy('nama')
            ->get();

        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();

        return view('guru.ekstrakurikuler.index', compact('eskuls', 'ta', 'sem'));
    }

    /**
     * Portal kerja Pembina untuk eskul tertentu
     */
    public function show(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);
        $ekstrakurikuler->load(['pembina', 'ketua']);

        $ta = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $sem = $request->input('semester', PengaturanSekolah::getActiveSemester());
        $tab = $request->input('tab', 'anggota');

        // 1. Anggota
        $anggotas = AnggotaEkstrakurikuler::with(['siswa.kelas'])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tahun_ajaran', $ta)
            ->where('semester', $sem)
            ->orderByRaw("FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Anggota')")
            ->get();

        // 2. Rencana Kegiatan
        $rencanas = RencanaKegiatanEskul::with('laporan')
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->orderBy('pertemuan_ke')
            ->get();

        // 3. Laporan Realisasi
        $laporans = LaporanKegiatanEskul::with(['rencanaKegiatan', 'presensis'])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->orderByDesc('tanggal_kegiatan')
            ->get();

        // 4. Presensi
        $tanggalList = PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->select('tanggal')
            ->distinct()
            ->orderByDesc('tanggal')
            ->pluck('tanggal');

        $selectedTanggal = $request->input('tanggal', $tanggalList->first() ?? date('Y-m-d'));

        $presensis = PresensiEskul::with('siswa.kelas')
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal', $selectedTanggal)
            ->get()
            ->keyBy('siswa_id');

        // Siswa yang tersedia untuk ditambahkan
        $existingSiswaIds = $anggotas->pluck('siswa_id')->toArray();
        $availableSiswas = User::where('role', 'siswa')
            ->where('is_active', true)
            ->whereNotIn('id', $existingSiswaIds)
            ->with('kelas')
            ->orderBy('name')
            ->get();

        $kelases = \App\Models\Kelas::where('is_aktif', true)->orderBy('nama_kelas')->get();
        $eskul = $ekstrakurikuler;

        return view('guru.ekstrakurikuler.show', compact(
            'ekstrakurikuler',
            'eskul',
            'anggotas',
            'rencanas',
            'laporans',
            'tanggalList',
            'selectedTanggal',
            'presensis',
            'availableSiswas',
            'kelases',
            'ta',
            'sem',
            'tab'
        ));
    }

    /**
     * Input Rencana Kegiatan Eskul
     */
    public function storeRencana(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);

        $validated = $request->validate([
            'pertemuan_ke'      => 'required|integer|min:1',
            'tipe_jadwal'       => 'nullable|in:minggu_efektif,kegiatan_tambahan',
            'minggu_ke'         => 'nullable|integer|min:1|max:30',
            'tanggal_rencana'   => 'required|date',
            'nama_kegiatan'     => 'required|string|max:255',
            'deskripsi_rencana' => 'nullable|string',
            'target_pencapaian' => 'nullable|string',
        ], [
            'pertemuan_ke.required'    => 'Pertemuan ke- wajib diisi.',
            'tanggal_rencana.required' => 'Tanggal rencana wajib diisi.',
            'nama_kegiatan.required'   => 'Nama/Materi kegiatan wajib diisi.',
        ]);

        $validated['tipe_jadwal']        = $request->input('tipe_jadwal', 'minggu_efektif');
        $validated['minggu_ke']          = $validated['tipe_jadwal'] === 'minggu_efektif' ? $request->input('minggu_ke') : null;
        $validated['ekstrakurikuler_id'] = $ekstrakurikuler->id;
        $validated['created_by']         = auth()->id();

        RencanaKegiatanEskul::create($validated);

        $label = $validated['tipe_jadwal'] === 'minggu_efektif' && $validated['minggu_ke']
            ? "Minggu Efektif ke-{$validated['minggu_ke']}"
            : "Kegiatan Tambahan Luar Jadwal";

        return back()->with('success', "Rencana kegiatan pertemuan ke-{$validated['pertemuan_ke']} ({$label}) berhasil disimpan!");
    }

    /**
     * Input Laporan Kegiatan Eskul
     * Seperti laporan guru (Laporan KBM)
     * Catatan: Tanggal sudah otomatis pada laporan ketika rencana sudah dipilih!
     */
    public function storeLaporan(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);

        $validated = $request->validate([
            'rencana_kegiatan_id' => 'nullable|exists:rencana_kegiatan_eskuls,id',
            'tipe_jadwal'         => 'nullable|in:minggu_efektif,kegiatan_tambahan',
            'minggu_ke'           => 'nullable|integer|min:1|max:30',
            'tanggal_kegiatan'    => 'required|date',
            'pertemuan_ke'        => 'required|integer|min:1',
            'nama_kegiatan'       => 'required|string|max:255',
            'ringkasan_materi'    => 'required|string',
            'status_pelaksanaan'  => 'required|in:sesuai_rencana,penyesuaian,terlaksana_penuh,ditunda',
            'catatan_kegiatan'    => 'nullable|string',
            'foto_dokumentasi'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'tanggal_kegiatan.required' => 'Tanggal pelaksanaan kegiatan wajib diisi.',
            'nama_kegiatan.required'    => 'Judul kegiatan wajib diisi.',
            'ringkasan_materi.required' => 'Ringkasan realisasi kegiatan wajib diisi.',
        ]);

        // Jika rencana_kegiatan_id dipilih, pastikan tanggal, tipe jadwal & materi sinkron
        if (!empty($validated['rencana_kegiatan_id'])) {
            $rencana = RencanaKegiatanEskul::find($validated['rencana_kegiatan_id']);
            if ($rencana) {
                if (empty($request->tanggal_kegiatan)) {
                    $validated['tanggal_kegiatan'] = $rencana->tanggal_rencana->format('Y-m-d');
                }
                if (empty($request->tipe_jadwal)) {
                    $validated['tipe_jadwal'] = $rencana->tipe_jadwal;
                }
                if (empty($request->minggu_ke)) {
                    $validated['minggu_ke'] = $rencana->minggu_ke;
                }
            }
        }

        $validated['tipe_jadwal'] = $validated['tipe_jadwal'] ?? 'minggu_efektif';
        if ($validated['tipe_jadwal'] !== 'minggu_efektif') {
            $validated['minggu_ke'] = null;
        }

        if ($request->hasFile('foto_dokumentasi')) {
            $path = $request->file('foto_dokumentasi')->store('eskul/dokumentasi', 'public');
            $validated['foto_dokumentasi'] = $path;
        }

        $validated['ekstrakurikuler_id'] = $ekstrakurikuler->id;
        $validated['created_by']         = auth()->id();

        // Hitung kehadiran awal dari presensi jika sudah ada yang terdata
        $presensiHariIni = PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal', $validated['tanggal_kegiatan'])
            ->get();

        $validated['jumlah_hadir'] = $presensiHariIni->where('status', 'Hadir')->count();
        $validated['jumlah_izin']  = $presensiHariIni->where('status', 'Izin')->count();
        $validated['jumlah_sakit'] = $presensiHariIni->where('status', 'Sakit')->count();
        $validated['jumlah_alpa']  = $presensiHariIni->where('status', 'Alpa')->count();

        $laporan = LaporanKegiatanEskul::create($validated);

        // Update relasi presensi pada tanggal tersebut agar terhubung ke laporan ini
        PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal', $validated['tanggal_kegiatan'])
            ->update([
                'laporan_kegiatan_id' => $laporan->id,
                'rencana_kegiatan_id' => $validated['rencana_kegiatan_id'] ?? null,
            ]);

        return back()->with('success', "Laporan kegiatan pertemuan ke-{$laporan->pertemuan_ke} ({$laporan->nama_kegiatan}) berhasil diarsipkan!");
    }

    /**
     * Input / Simpan Presensi Pertemuan Eskul oleh Pembina
     */
    public function storePresensi(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);

        $request->validate([
            'tanggal'        => 'required|date',
            'presensi'       => 'required|array',
            'presensi.*'     => 'in:Hadir,Izin,Sakit,Alpa',
            'keterangan'     => 'nullable|array',
            'keterangan.*'   => 'nullable|string|max:255',
        ]);

        $tanggal = $request->tanggal;

        // Cek apakah ada laporan atau rencana pada tanggal ini
        $laporan = LaporanKegiatanEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal_kegiatan', $tanggal)
            ->first();

        $rencana = RencanaKegiatanEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal_rencana', $tanggal)
            ->first();

        DB::beginTransaction();
        try {
            foreach ($request->presensi as $siswaId => $status) {
                $ket = $request->keterangan[$siswaId] ?? null;

                PresensiEskul::updateOrCreate(
                    [
                        'ekstrakurikuler_id' => $ekstrakurikuler->id,
                        'tanggal'            => $tanggal,
                        'siswa_id'           => $siswaId,
                    ],
                    [
                        'laporan_kegiatan_id' => $laporan?->id,
                        'rencana_kegiatan_id' => $rencana?->id,
                        'status'              => $status,
                        'keterangan'          => $ket,
                        'metode_absen'        => 'pembina',
                        'diinput_oleh'        => auth()->id(),
                    ]
                );

                // Sinkronkan absensi siswa ke mapel Team Work Project dan Project Pancasila
                \App\Services\EskulSyncService::syncPresensiSiswa($ekstrakurikuler, $tanggal, (int)$siswaId, $status, $ket);
            }

            // Update agregat di laporan jika ada
            if ($laporan) {
                $rekap = PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
                    ->where('tanggal', $tanggal)
                    ->get();

                $laporan->update([
                    'jumlah_hadir' => $rekap->where('status', 'Hadir')->count(),
                    'jumlah_izin'  => $rekap->where('status', 'Izin')->count(),
                    'jumlah_sakit' => $rekap->where('status', 'Sakit')->count(),
                    'jumlah_alpa'  => $rekap->where('status', 'Alpa')->count(),
                ]);
            }

            DB::commit();
            return back()->with('success', "Presensi eskul tanggal {$tanggal} berhasil disimpan dan disinkronkan ke Mapel Team Work Project & Project Pancasila!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan presensi: ' . $e->getMessage());
        }
    }

    /**
     * Input Nilai Eskul oleh Pembina
     */
    public function storeNilai(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);

        $request->validate([
            'nilai_angka'   => 'nullable|array',
            'nilai_angka.*' => 'nullable|numeric|min:0|max:100',
            'nilai_huruf'   => 'nullable|array',
            'nilai_huruf.*' => 'nullable|in:A,B,C,D',
            'catatan'       => 'nullable|array',
            'catatan.*'     => 'nullable|string|max:500',
            'nilai'         => 'nullable|array',
        ]);

        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();

        DB::beginTransaction();
        try {
            $anggotas = AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
                ->where('tahun_ajaran', $ta)
                ->where('semester', $sem)
                ->get();

            foreach ($anggotas as $anggota) {
                $siswaId = $anggota->siswa_id;

                // Support baik format direct maupun nested array
                $angka = null;
                if (isset($request->nilai_angka[$siswaId]) && $request->nilai_angka[$siswaId] !== '') {
                    $angka = (float)$request->nilai_angka[$siswaId];
                } elseif (isset($request->nilai[$anggota->id]['nilai_angka']) && $request->nilai[$anggota->id]['nilai_angka'] !== '') {
                    $angka = (float)$request->nilai[$anggota->id]['nilai_angka'];
                }

                $huruf = $request->nilai_huruf[$siswaId] ?? ($request->nilai[$anggota->id]['nilai_huruf'] ?? null);
                if (empty($huruf) && $angka !== null) {
                    if ($angka >= 86) $huruf = 'A';
                    elseif ($angka >= 76) $huruf = 'B';
                    elseif ($angka >= 65) $huruf = 'C';
                    else $huruf = 'D';
                }

                $catatan = $request->catatan[$siswaId] ?? ($request->nilai[$anggota->id]['catatan'] ?? null);

                $anggota->update([
                    'nilai_angka'   => $angka,
                    'nilai_huruf'   => $huruf,
                    'catatan_nilai' => $catatan,
                ]);

                // Sinkronkan nilai ke mapel Team Work Project dan Project Pancasila di kelas masing-masing
                if ($angka !== null || !empty($huruf)) {
                    \App\Services\EskulSyncService::syncNilaiSiswa($ekstrakurikuler, (int)$siswaId, $angka, $huruf, $catatan);
                }
            }

            DB::commit();
            return back()->with('success', 'Nilai ekstrakurikuler siswa berhasil diperbarui dan disinkronkan ke Mapel Team Work Project & Project Pancasila!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan nilai eskul: ' . $e->getMessage());
        }
    }

    /**
     * Tambah anggota dari portal pembina
     */
    public function storeAnggota(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);

        $request->validate([
            'siswa_ids'   => 'required|array|min:1',
            'siswa_ids.*' => 'exists:users,id',
            'jabatan'     => 'nullable|string|max:50',
        ]);

        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();
        $jabatan = $request->input('jabatan', 'Anggota');

        $added = 0;
        foreach ($request->siswa_ids as $siswaId) {
            $anggota = AnggotaEkstrakurikuler::firstOrCreate(
                [
                    'ekstrakurikuler_id' => $ekstrakurikuler->id,
                    'siswa_id'           => $siswaId,
                    'tahun_ajaran'       => $ta,
                    'semester'           => $sem,
                ],
                [
                    'jabatan' => $jabatan,
                    'status'  => 'aktif',
                ]
            );
            if ($anggota->wasRecentlyCreated) $added++;
        }

        return back()->with('success', "Berhasil menambahkan {$added} siswa ke dalam {$ekstrakurikuler->nama}.");
    }

    /**
     * Hapus anggota dari eskul
     */
    public function destroyAnggota(Ekstrakurikuler $ekstrakurikuler, AnggotaEkstrakurikuler $anggota)
    {
        $this->authorizePembina($ekstrakurikuler);

        if ($ekstrakurikuler->ketua_siswa_id === $anggota->siswa_id) {
            $ekstrakurikuler->update(['ketua_siswa_id' => null]);
        }

        $anggota->delete();
        return back()->with('success', 'Siswa berhasil dikeluarkan dari eskul.');
    }

    /**
     * Pembina menugaskan siswa jadi Ketua Eskul untuk mengabsen
     */
    public function setKetua(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $this->authorizePembina($ekstrakurikuler);

        $request->validate([
            'siswa_id' => 'required|exists:users,id',
        ]);

        $siswaId = (int)$request->siswa_id;

        AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('jabatan', 'Ketua')
            ->update(['jabatan' => 'Anggota']);

        AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('siswa_id', $siswaId)
            ->update(['jabatan' => 'Ketua']);

        $ekstrakurikuler->update(['ketua_siswa_id' => $siswaId]);

        $siswa = User::find($siswaId);
        return back()->with('success', "{$siswa->name} resmi ditugaskan sebagai Ketua {$ekstrakurikuler->nama} untuk membantu mengabsen!");
    }
}
