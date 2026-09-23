<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KalenderAkademik;
use App\Models\KalenderAkademikEvent;
use App\Models\PengaturanSekolah;
use App\Services\GoogleHolidaySyncService;
use Carbon\Carbon;

class KalenderAkademikController extends Controller
{
    /**
     * Tampilan Kalender Akademik 1 Tahun (Semester 1 & 2)
     */
    public function index(Request $request, GoogleHolidaySyncService $holidayService)
    {
        $tahunAjaran = $request->query('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        // Normalisasi format tahun
        $tahunAjaran = str_replace([' - ', ' '], ['/', ''], $tahunAjaran);
        if (strpos($tahunAjaran, '/') === false || $tahunAjaran === '2025/2026') {
            $tahunAjaran = PengaturanSekolah::getActiveTahunAjaran();
        }

        $semester = $request->query('semester', '1'); // '1' atau '2'

        // Cari atau buat otomatis kalender akademik awal jika belum ada
        $kalender = KalenderAkademik::where('tahun_ajaran', $tahunAjaran)->first();
        if (!$kalender) {
            $parts = explode('/', $tahunAjaran);
            $yStart = (int)($parts[0] ?? 2026);
            $yEnd = (int)($parts[1] ?? ($yStart + 1));

            $kalender = KalenderAkademik::create([
                'tahun_ajaran'         => $tahunAjaran,
                'nama_kalender'        => "Kalender Pendidikan & Akademik SMK Plus Al-Hilal {$tahunAjaran}",
                'tanggal_mulai'        => "{$yStart}-07-01",
                'tanggal_selesai'      => "{$yEnd}-06-30",
                'tanggal_mulai_smt1'   => "{$yStart}-07-01",
                'tanggal_selesai_smt1' => "{$yStart}-12-31",
                'tanggal_mulai_smt2'   => "{$yEnd}-01-01",
                'tanggal_selesai_smt2' => "{$yEnd}-06-30",
                'is_aktif'             => true,
                'created_by_user_id'   => Auth::id(),
                'deskripsi'            => "Kalender resmi akademik SMK Plus Al-Hilal Arjawinangun Tahun Ajaran {$tahunAjaran}.",
            ]);

            // Otomatis tarik hari libur nasional & muat agenda resmi standar Disdik Jabar
            $holidayService->syncHolidays($kalender);
            $holidayService->seedDisdikJabarOfficialEvents($kalender);
        }

        // Generate matriks bulanan untuk semester yang aktif
        $matrix = $kalender->generateMonthlyMatrix($semester);

        // Ambil event terurut untuk semester ini
        $events = $kalender->events()
            ->where('semester', (string)$semester)
            ->orderBy('tanggal_mulai')
            ->get();

        // Hitung estimasi minggu efektif
        $efektifPerBulan = $kalender->calculateEfektifPerBulan($semester === '1' ? 'ganjil' : 'genap');

        // Daftar tahun ajaran yang tersedia di sistem
        $allKalenders = KalenderAkademik::orderBy('tahun_ajaran', 'desc')->get();

        // Data kop sekolah
        $schoolInfo = [
            'nama'     => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal'),
            'npsn'     => PengaturanSekolah::get('npsn', '69758451'),
            'alamat'   => PengaturanSekolah::get('alamat_sekolah', 'Jl. H. Manshur No 7 Lap. Bima Rembes, Ds. Tegalgubug, Kec. Arjawinangun, Kab. Cirebon'),
            'kepsek'   => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepsek' => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'waka_kur' => PengaturanSekolah::get('nama_waka_kurikulum', 'Sokid, S.T, M.Kom'),
            'nip_waka' => PengaturanSekolah::get('nip_waka_kurikulum', '198501012010011005'),
            'logo'     => PengaturanSekolah::get('logo_sekolah', 'images/logo.png'),
            'titimangsa' => PengaturanSekolah::get('titimangsa', 'Arjawinangun, 1 Juli 2026'),
        ];

        return view('admin.kalender.index', compact(
            'kalender', 
            'matrix', 
            'events', 
            'semester', 
            'tahunAjaran', 
            'efektifPerBulan',
            'allKalenders',
            'schoolInfo'
        ));
    }

    protected function checkCanManage(): void
    {
        if (!Auth::check() || !Auth::user()->canManageAcademic()) {
            abort(403, 'Akses ditolak. Anda hanya memiliki izin untuk melihat Kalender Akademik.');
        }
    }

    /**
     * Simpan Kalender Akademik Baru
     */
    public function store(Request $request, GoogleHolidaySyncService $holidayService)
    {
        $this->checkCanManage();

        $validated = $request->validate([
            'tahun_ajaran'    => 'required|string|max:30|unique:kalender_akademiks,tahun_ajaran',
            'nama_kalender'   => 'required|string|max:150',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'deskripsi'       => 'nullable|string',
        ]);

        $kalender = KalenderAkademik::create([
            'tahun_ajaran'         => $validated['tahun_ajaran'],
            'nama_kalender'        => $validated['nama_kalender'],
            'tanggal_mulai'        => $validated['tanggal_mulai'],
            'tanggal_selesai'      => $validated['tanggal_selesai'],
            'tanggal_mulai_smt1'   => $validated['tanggal_mulai'],
            'tanggal_selesai_smt1' => Carbon::parse($validated['tanggal_mulai'])->addMonths(5)->endOfMonth()->format('Y-m-d'),
            'tanggal_mulai_smt2'   => Carbon::parse($validated['tanggal_mulai'])->addMonths(6)->startOfMonth()->format('Y-m-d'),
            'tanggal_selesai_smt2' => $validated['tanggal_selesai'],
            'is_aktif'             => true,
            'deskripsi'            => $validated['deskripsi'] ?? null,
            'created_by_user_id'   => Auth::id(),
        ]);

        // Auto sync hari libur nasional
        $holidayService->syncHolidays($kalender);
        $holidayService->seedDisdikJabarOfficialEvents($kalender);

        return redirect()->route('admin.kalender.index', ['tahun_ajaran' => $kalender->tahun_ajaran])
            ->with('success', "Kalender Akademik {$kalender->tahun_ajaran} berhasil dibuat lengkap dengan hari libur nasional & agenda resmi sekolah!");
    }

    /**
     * Update Pengaturan Kalender Akademik
     */
    public function update(Request $request, KalenderAkademik $kalender)
    {
        $this->checkCanManage();

        $validated = $request->validate([
            'nama_kalender'   => 'required|string|max:150',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'deskripsi'       => 'nullable|string',
        ]);

        $kalender->update($validated);

        return back()->with('success', 'Konfigurasi Kalender Akademik berhasil diperbarui!');
    }

    /**
     * Tambah Event / Hari Libur / Agenda Baru
     */
    public function storeEvent(Request $request, KalenderAkademik $kalender)
    {
        $this->checkCanManage();
        $validated = $request->validate([
            'judul_kegiatan'  => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'semester'        => 'required|in:1,2',
            'kategori'        => 'required|string',
            'warna_bg'        => 'required|string',
            'keterangan'      => 'nullable|string',
            'is_libur'        => 'nullable|boolean',
        ]);

        $kalender->events()->create([
            'judul_kegiatan'  => $validated['judul_kegiatan'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?: $validated['tanggal_mulai'],
            'semester'        => $validated['semester'],
            'kategori'        => $validated['kategori'],
            'warna_bg'        => $validated['warna_bg'],
            'keterangan'      => $validated['keterangan'] ?? null,
            'is_libur'        => (bool)($validated['is_libur'] ?? in_array($validated['kategori'], ['libur_nasional', 'libur_sekolah', 'libur_semester'])),
            'sumber'          => 'manual',
        ]);

        return back()->with('success', "Agenda \"{$validated['judul_kegiatan']}\" berhasil ditambahkan ke kalender akademik!");
    }

    /**
     * Update Event
     */
    public function updateEvent(Request $request, KalenderAkademikEvent $event)
    {
        $this->checkCanManage();

        $validated = $request->validate([
            'judul_kegiatan'  => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'semester'        => 'required|in:1,2',
            'kategori'        => 'required|string',
            'warna_bg'        => 'required|string',
            'keterangan'      => 'nullable|string',
            'is_libur'        => 'nullable|boolean',
        ]);

        $event->update([
            'judul_kegiatan'  => $validated['judul_kegiatan'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?: $validated['tanggal_mulai'],
            'semester'        => $validated['semester'],
            'kategori'        => $validated['kategori'],
            'warna_bg'        => $validated['warna_bg'],
            'keterangan'      => $validated['keterangan'] ?? null,
            'is_libur'        => (bool)($validated['is_libur'] ?? in_array($validated['kategori'], ['libur_nasional', 'libur_sekolah', 'libur_semester'])),
        ]);

        return back()->with('success', "Agenda \"{$event->judul_kegiatan}\" berhasil diperbarui!");
    }

    /**
     * Hapus Event
     */
    public function destroyEvent(KalenderAkademikEvent $event)
    {
        $this->checkCanManage();

        $judul = $event->judul_kegiatan;
        $event->delete();

        return back()->with('success', "Agenda \"{$judul}\" berhasil dihapus dari kalender!");
    }

    /**
     * Tarik Ulang Hari Libur Nasional dari Google Calendar Feed
     */
    public function syncGoogleHolidays(KalenderAkademik $kalender, GoogleHolidaySyncService $service)
    {
        $this->checkCanManage();

        $res = $service->syncHolidays($kalender);

        return back()->with('success', "Berhasil menyinkronkan hari libur nasional dari Google Calendar! ({$res['added']} baru, {$res['updated']} diperbarui).");
    }

    /**
     * Pulihkan Agenda Resmi Standar Disdik Jawa Barat
     */
    public function seedOfficialEvents(KalenderAkademik $kalender, GoogleHolidaySyncService $service)
    {
        $this->checkCanManage();

        $count = $service->seedDisdikJabarOfficialEvents($kalender);

        return back()->with('success', "Berhasil memuat {$count} butir agenda pendidikan resmi sekolah (MPLS, Asesmen, Rapor, Libur Ramadhan/Semester)!");
    }

    /**
     * Download Format Template CSV Siap Pakai untuk Import Kalender Akademik
     */
    public function downloadTemplateCsv()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_kalender_akademik.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // Tulis UTF-8 BOM agar terbaca sempurna di Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header Kolom
            fputcsv($handle, [
                'tanggal_mulai',
                'tanggal_selesai',
                'judul_kegiatan',
                'kategori',
                'semester',
                'is_libur',
                'keterangan',
            ]);

            // Contoh data pengisian agenda yang realistis
            $samples = [
                ['2026-07-13', '2026-07-15', 'Masa Pengenalan Lingkungan Sekolah (MPLS)', 'kegiatan_sekolah', '1', '0', 'Pengenalan lingkungan sekolah peserta didik baru'],
                ['2026-08-17', '2026-08-17', 'Hari Kemerdekaan Republik Indonesia ke-81', 'libur_nasional', '1', '1', 'Libur Nasional HUT Kemerdekaan RI'],
                ['2026-09-21', '2026-09-26', 'Asesmen Tengah Semester (ATS) Ganjil', 'ujian_asesmen', '1', '0', 'Ujian asesmen capaian belajar semester 1'],
                ['2026-10-14', '2026-10-15', 'Kunjungan Industri (KI) Kelas XI ke DUDI', 'kegiatan_sekolah', '1', '0', 'Agenda Waka Hubin & Hubungan Industri'],
                ['2026-12-14', '2026-12-18', 'Pekan Olahraga Antar Kelas & Seni (Classmeeting)', 'kegiatan_sekolah', '1', '0', 'Kegiatan Kesiswaan dan Pengurus OSIS'],
                ['2026-12-24', '2026-12-24', 'Pembagian Rapor Semester Ganjil', 'pembagian_rapor', '1', '0', 'Penyerahan laporan hasil belajar kepada orang tua siswa'],
                ['2026-12-28', '2027-01-09', 'Libur Akhir Semester Ganjil', 'libur_semester', '1', '1', 'Libur pergantian semester siswa'],
            ];

            foreach ($samples as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Banyak Kegiatan ke Kalender Akademik (File CSV / Excel / Raw Text Batch)
     */
    public function importEvents(Request $request, KalenderAkademik $kalender)
    {
        $this->checkCanManage();

        $rows = [];

        // 1. Jika ada berkas CSV / Excel diunggah
        if ($request->hasFile('file_import')) {
            $file = $request->file('file_import');
            $ext = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['csv', 'txt'])) {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($handle);
                    }
                    while (($data = fgetcsv($handle, 2000, ",")) !== false) {
                        if (count($data) === 1 && str_contains($data[0], ';')) {
                            $data = str_getcsv($data[0], ';');
                        }
                        if (!empty(array_filter($data))) {
                            $rows[] = $data;
                        }
                    }
                    fclose($handle);
                }
            } elseif (in_array($ext, ['xlsx', 'xls'])) {
                try {
                    $collection = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $file);
                    if ($collection->isNotEmpty() && $collection->first()->isNotEmpty()) {
                        foreach ($collection->first() as $rowCollection) {
                            $rows[] = $rowCollection->toArray();
                        }
                    }
                } catch (\Throwable $e) {
                    return back()->with('error', 'Gagal membaca berkas Excel: ' . $e->getMessage());
                }
            } else {
                return back()->with('error', 'Format berkas tidak didukung. Harap unggah berkas .csv atau .xlsx');
            }
        } elseif ($request->filled('batch_text')) {
            // 2. Jika diinput via Salin-Tempel Teks Batch dari spreadsheet / clipboard
            $lines = preg_split('/\r\n|\r|\n/', trim($request->input('batch_text')));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (str_contains($line, "\t")) {
                    $parts = explode("\t", $line);
                } elseif (str_contains($line, ";")) {
                    $parts = str_getcsv($line, ";");
                } elseif (str_contains($line, "|")) {
                    $parts = array_map('trim', explode("|", $line));
                } else {
                    $parts = str_getcsv($line, ",");
                }
                $rows[] = $parts;
            }
        } else {
            return back()->with('error', 'Harap pilih berkas CSV/Excel atau tempel teks data kegiatan untuk diimpor.');
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada data kegiatan yang terbaca untuk diimpor.');
        }

        $importedCount = 0;
        $allowedKategori = [
            'libur_nasional', 
            'libur_sekolah', 
            'libur_semester', 
            'kegiatan_sekolah', 
            'ujian_asesmen', 
            'pembagian_rapor',
            'hari_efektif_khusus'
        ];

        foreach ($rows as $cols) {
            $cols = array_values(array_map('trim', (array)$cols));

            $c0 = strtolower($cols[0] ?? '');
            if (str_contains($c0, 'tanggal') || str_contains($c0, 'mulai') || str_contains($c0, 'start') || $c0 === 'tgl') {
                continue;
            }

            if (empty($cols[0])) continue;

            $tglMulai = $this->parseDateFlexible($cols[0]);
            if (!$tglMulai) continue;

            $tglSelesai = !empty($cols[1]) ? $this->parseDateFlexible($cols[1]) : $tglMulai;
            if (!$tglSelesai) $tglSelesai = $tglMulai;

            $judul = $cols[2] ?? null;
            if (empty($judul)) continue;

            $rawKategori = strtolower($cols[3] ?? 'kegiatan_sekolah');
            $kategori = 'kegiatan_sekolah';
            foreach ($allowedKategori as $ak) {
                if (str_contains($rawKategori, $ak) || $rawKategori === $ak) {
                    $kategori = $ak;
                    break;
                }
            }
            if (str_contains($rawKategori, 'nasional')) $kategori = 'libur_nasional';
            elseif (str_contains($rawKategori, 'semester') && str_contains($rawKategori, 'libur')) $kategori = 'libur_semester';
            elseif (str_contains($rawKategori, 'libur')) $kategori = 'libur_sekolah';
            elseif (str_contains($rawKategori, 'ujian') || str_contains($rawKategori, 'asesmen') || str_contains($rawKategori, 'pts') || str_contains($rawKategori, 'pas')) $kategori = 'ujian_asesmen';
            elseif (str_contains($rawKategori, 'rapor') || str_contains($rawKategori, 'raport')) $kategori = 'pembagian_rapor';
            elseif (str_contains($rawKategori, 'efektif')) $kategori = 'hari_efektif_khusus';

            $semester = $cols[4] ?? null;
            if (!in_array($semester, ['1', '2'])) {
                $m = (int)date('n', strtotime($tglMulai));
                $semester = ($m >= 7 && $m <= 12) ? '1' : '2';
            }

            $rawLibur = strtolower($cols[5] ?? '');
            $isLibur = in_array($rawLibur, ['1', 'true', 'ya', 'libur', 'yes']) || in_array($kategori, ['libur_nasional', 'libur_sekolah', 'libur_semester']);

            $keterangan = $cols[6] ?? null;

            $warnaBg = 'yellow';
            if ($isLibur || in_array($kategori, ['libur_nasional', 'libur_sekolah', 'libur_semester'])) {
                $warnaBg = 'red';
            } elseif ($kategori === 'ujian_asesmen') {
                $warnaBg = 'blue';
            } elseif ($kategori === 'pembagian_rapor') {
                $warnaBg = 'cyan';
            } elseif ($kategori === 'hari_efektif_khusus') {
                $warnaBg = 'emerald';
            } else {
                $judulLower = strtolower($judul);
                if (str_contains($judulLower, 'hubin') || str_contains($judulLower, 'industri') || str_contains($judulLower, 'pkl')) {
                    $warnaBg = 'purple';
                } elseif (str_contains($judulLower, 'osis') || str_contains($judulLower, 'olahraga') || str_contains($judulLower, 'ldks') || str_contains($judulLower, 'bazzar') || str_contains($judulLower, 'bazar')) {
                    $warnaBg = 'emerald';
                }
            }

            KalenderAkademikEvent::create([
                'kalender_akademik_id' => $kalender->id,
                'tanggal_mulai'        => $tglMulai,
                'tanggal_selesai'      => $tglSelesai,
                'semester'             => (string)$semester,
                'judul_kegiatan'       => $judul,
                'kategori'             => $kategori,
                'warna_bg'             => $warnaBg,
                'keterangan'           => $keterangan,
                'is_libur'             => (bool)$isLibur,
                'sumber'               => 'import',
            ]);

            $importedCount++;
        }

        return back()->with('success', "Alhamdulillah, berhasil mengimpor {$importedCount} butir kegiatan ke Kalender Akademik Tahun Ajaran {$kalender->tahun_ajaran}!");
    }

    /**
     * Helper fleksibel parsing string tanggal (YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY)
     */
    private function parseDateFlexible(string $dateStr): ?string
    {
        $d = trim($dateStr);
        if (empty($d)) return null;

        try {
            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $d)) {
                return Carbon::parse($d)->format('Y-m-d');
            }
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $d)) {
                return Carbon::createFromFormat('d/m/Y', $d)->format('Y-m-d');
            }
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $d)) {
                return Carbon::createFromFormat('d-m-Y', $d)->format('Y-m-d');
            }
            return Carbon::parse($d)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Cetak Kalender Pendidikan Resmi (Format 1:1 Lembar Cetak Dinas dengan KOP Sekolah)
     */
    public function print(Request $request, KalenderAkademik $kalender)
    {
        $semester = $request->query('semester', '1'); // '1', '2', atau 'all'

        $matrixSmt1 = $kalender->generateMonthlyMatrix('1');
        $matrixSmt2 = $kalender->generateMonthlyMatrix('2');

        $eventsSmt1 = $kalender->events()->where('semester', '1')->orderBy('tanggal_mulai')->get();
        $eventsSmt2 = $kalender->events()->where('semester', '2')->orderBy('tanggal_mulai')->get();

        $schoolInfo = [
            'nama'       => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal'),
            'npsn'       => PengaturanSekolah::get('npsn', '69758451'),
            'alamat'     => PengaturanSekolah::get('alamat_sekolah', 'Jl. H. Manshur No 7 Lap. Bima Rembes, Ds. Tegalgubug, Kec. Arjawinangun, Kab. Cirebon'),
            'kepsek'     => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepsek' => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'waka_kur'   => PengaturanSekolah::get('nama_waka_kurikulum', 'Sokid, S.T, M.Kom'),
            'nip_waka'   => PengaturanSekolah::get('nip_waka_kurikulum', '198501012010011005'),
            'logo'       => PengaturanSekolah::get('logo_sekolah', 'images/logo.png'),
            'titimangsa' => PengaturanSekolah::get('titimangsa', 'Arjawinangun, 1 Juli 2026'),
        ];

        return view('admin.kalender.print', compact(
            'kalender',
            'semester',
            'matrixSmt1',
            'matrixSmt2',
            'eventsSmt1',
            'eventsSmt2',
            'schoolInfo'
        ));
    }

    /**
     * Endpoint API JSON untuk modul Minggu Efektif Guru
     */
    public function getCalculatedEfektif(Request $request, ?KalenderAkademik $kalender = null)
    {
        if (!$kalender || !$kalender->exists) {
            $tahun = $request->query('tahun_ajaran');
            $kalender = $tahun
                ? KalenderAkademik::where('tahun_ajaran', $tahun)->first()
                : KalenderAkademik::where('is_aktif', true)->latest()->first();
        }

        if (!$kalender) {
            $kalender = KalenderAkademik::latest()->first();
        }

        if (!$kalender) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kalender akademik belum tersedia.',
            ], 404);
        }

        $semester = $request->query('semester', 'ganjil');

        if ($request->has('hari')) {
            $hari = $request->query('hari');
            $jp = intval($request->query('jp', 4));
            $dataByHari = $kalender->calculateEfektifByHari($hari, $semester, $jp);

            return response()->json([
                'status'          => 'success',
                'mode'            => 'by_hari',
                'kalender_id'     => $kalender->id,
                'tahun_ajaran'    => $kalender->tahun_ajaran,
                'semester'        => $semester,
                'data'            => $dataByHari,
            ]);
        }

        $data = $kalender->calculateEfektifPerBulan($semester);

        return response()->json([
            'status'          => 'success',
            'mode'            => 'general',
            'kalender_id'     => $kalender->id,
            'tahun_ajaran'    => $kalender->tahun_ajaran,
            'semester'        => $semester,
            'bulans'          => $data,
        ]);
    }
}
