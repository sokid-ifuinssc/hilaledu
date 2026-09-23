<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kurikulum;
use App\Models\MataPelajaran;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KurikulumController extends Controller
{
    /**
     * Tampilkan Daftar Alokasi Kurikulum & Beban Mengajar KBM
     */
    public function index(Request $request)
    {
        $jurusan = $request->query('jurusan', 'all');
        $jenjang = $request->query('jenjang', 'all');
        $kelas = $request->query('kelas', 'all');
        $search = $request->query('search', '');

        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2025 - 2026');
        $semester = PengaturanSekolah::get('semester', 'Ganjil');

        $query = Kurikulum::with(['mataPelajaran', 'guru', 'kelasModel', 'jurusanModel'])
            ->where('is_aktif', true);

        if ($jurusan && $jurusan !== 'all') {
            $query->where('jurusan', $jurusan);
        }

        if ($jenjang && $jenjang !== 'all') {
            $query->where('jenjang', $jenjang);
        }

        if ($kelas && $kelas !== 'all') {
            $query->where('kelas', $kelas);
        }

        if (!empty($search)) {
            $query->whereHas('mataPelajaran', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        // Urutkan berdasarkan Jenjang (X, XI, XII), Kelas, Kategori (A lalu B), Sub Kategori, dan Urutan
        $kurikulums = $query->orderByRaw("CASE jenjang WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('kelas')
            ->orderByRaw("CASE COALESCE(kategori, 'A. KELOMPOK MATA PELAJARAN UMUM') WHEN 'A. KELOMPOK MATA PELAJARAN UMUM' THEN 1 WHEN 'B. KELOMPOK MATA PELAJARAN KEJURUAN' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE COALESCE(sub_kategori, '') WHEN 'Dasar-dasar Program Keahlian' THEN 1 WHEN 'Mata Pelajaran [Konsentrasi Keahlian]***' THEN 2 WHEN 'Mata Pelajaran Pilihan****' THEN 3 ELSE 4 END")
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        // Ringkasan KPI Statistik
        $allActive = Kurikulum::where('is_aktif', true)->get();
        $totalAlokasiJp = $allActive->sum('alokasi_jam');
        $totalMapelDiampu = $allActive->count();
        $totalGuruPengampu = $allActive->whereNotNull('guru_user_id')->pluck('guru_user_id')->unique()->count();
        $totalRombel = $allActive->pluck('kelas')->unique()->count();

        // Data Master Pilihan untuk Filter dan Modal
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();
        $jurusans = Jurusan::orderBy('kode')->get();
        $jenjangList = ['X', 'XI', 'XII'];
        $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();
        $guruList = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();

        return view('admin.kurikulum.index', compact(
            'kurikulums',
            'jurusan',
            'jenjang',
            'kelas',
            'search',
            'tahunAjaran',
            'semester',
            'totalAlokasiJp',
            'totalMapelDiampu',
            'totalGuruPengampu',
            'totalRombel',
            'kelasList',
            'jurusans',
            'jenjangList',
            'mapels',
            'guruList'
        ));
    }

    /**
     * Auto Generate Kurikulum dari Master Mata Pelajaran & Kelas
     */
    public function autoGenerate(Request $request)
    {
        $mapels = MataPelajaran::where('is_aktif', true)->get();
        $kelasList = Kelas::with('jurusan')->where('is_aktif', true)->get();
        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2025 - 2026');
        $semester = PengaturanSekolah::get('semester', 'Ganjil');

        $created = 0;
        DB::beginTransaction();
        try {
            foreach ($kelasList as $kelas) {
                foreach ($mapels as $mapel) {
                    // Cek tingkat (X, XI, XII atau semua)
                    if ($mapel->tingkat && strtolower($mapel->tingkat) !== 'semua' && $mapel->tingkat !== $kelas->tingkat) {
                        continue;
                    }

                    // Deteksi mapel kejuruan dan pencocokan jurusan
                    $mapelName = strtoupper($mapel->nama . ' ' . $mapel->kode);
                    $kelasName = strtoupper($kelas->nama_kelas ?? $kelas->nama);
                    
                    $jurusans = ['TKJT', 'AKL', 'TO', 'DKV', 'BDP', 'OTKP', 'RPL', 'TKR', 'TSM', 'TBSM'];
                    $mapelJurusan = null;
                    foreach ($jurusans as $j) {
                        // Jika Mapel mengandung nama jurusan (sebagai kejuruan), tandai
                        if (str_contains($mapelName, $j) || str_contains(strtoupper($mapel->kategori ?? ''), $j)) {
                            $mapelJurusan = $j;
                            break;
                        }
                    }
                    
                    // Cek jika ini mapel kejuruan
                    $isKejuruan = str_contains(strtoupper($mapel->kategori ?? ''), 'KEJURUAN') || str_contains(strtoupper($mapel->kelompok ?? ''), 'KEJURUAN');

                    // Jika mapel ini khusus jurusan X, tapi kelas ini BUKAN jurusan X, lewati
                    if ($mapelJurusan && !str_contains($kelasName, $mapelJurusan)) {
                        continue;
                    }
                    
                    // Cegah bug: Jika mapel KEJURUAN tapi tidak ada penanda jurusannya, JANGAN masukkan ke semua kelas otomatis
                    if ($isKejuruan && !$mapelJurusan) {
                        continue;
                    }

                    $namaKelas = $kelas->nama_kelas ?? $kelas->nama;

                    $exists = Kurikulum::where('kelas', $namaKelas)
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->exists();

                    if (!$exists) {
                        Kurikulum::create([
                            'mata_pelajaran_id' => $mapel->id,
                            'kategori'          => $mapel->kategori,
                            'sub_kategori'      => $mapel->sub_kategori,
                            'urutan'            => $mapel->urutan ?? 99,
                            'kelas'             => $namaKelas,
                            'jenjang'           => $kelas->tingkat,
                            'jurusan'           => $kelas->jurusan->kode ?? 'UMUM',
                            'tahun_ajaran'      => $tahunAjaran,
                            'semester'          => $semester,
                            'guru_user_id'      => $mapel->guru_user_id,
                            'alokasi_jam'       => $mapel->jam_per_minggu ?? 2,
                            'is_aktif'          => true,
                            'status_jadwal'     => 'Belum Terjadwal',
                        ]);
                        $created++;
                    }
                }
            }
            DB::commit();
            return back()->with('success', "Berhasil sinkronisasi otomatis. {$created} data kurikulum baru telah ditambahkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Gagal melakukan sinkronisasi: " . $e->getMessage());
        }
    }

    /**
     * Simpan Alokasi Kurikulum Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas'             => 'required|string|max:50',
            'mata_pelajaran_id' => 'required',
            'guru_user_id'      => 'nullable|exists:users,id',
            'alokasi_jam'       => 'required|integer|min:1|max:24',
            'keterangan'        => 'nullable|string|max:255',
            'kategori'          => 'nullable|string|max:100',
            'sub_kategori'      => 'nullable|string|max:100',
        ]);

        $kelasNama = trim($request->input('kelas'));
        $mapelInput = $request->input('mata_pelajaran_id');
        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2025 - 2026');
        $semester = PengaturanSekolah::get('semester', 'Ganjil');

        // Deteksi Jenjang & Jurusan dari nama kelas
        $jenjang = 'X';
        if (str_starts_with($kelasNama, 'XII')) {
            $jenjang = 'XII';
        } elseif (str_starts_with($kelasNama, 'XI')) {
            $jenjang = 'XI';
        }

        $jurusan = 'TKJT';
        if (str_contains($kelasNama, 'TO')) {
            $jurusan = 'TO';
        } elseif (str_contains($kelasNama, 'AKL')) {
            $jurusan = 'AKL';
        }

        $kelasModel = Kelas::where('nama', $kelasNama)->first();
        $jurusanModel = Jurusan::where('kode', $jurusan)->first();

        // Cari atau buat mapel baru
        $mapelModel = MataPelajaran::find($mapelInput);
        if (!$mapelModel) {
            $mapelModel = MataPelajaran::firstOrCreate(
                ['nama' => $mapelInput],
                [
                    'kode' => strtoupper(substr(preg_replace('/[^A-Za-z0-9\-]/', '', $mapelInput), 0, 5)) . '-' . rand(100, 999),
                    'kategori' => 'A. KELOMPOK MATA PELAJARAN UMUM',
                    'is_aktif' => true,
                    'jam_per_minggu' => (int) $request->input('alokasi_jam')
                ]
            );
        }
        
        $mapelId = $mapelModel->id;
        $kategori = $request->filled('kategori') ? $request->input('kategori') : ($mapelModel->kategori ?? 'A. KELOMPOK MATA PELAJARAN UMUM');
        $subKategori = $request->filled('sub_kategori') ? $request->input('sub_kategori') : $mapelModel->sub_kategori;
        $urutan = $mapelModel->urutan ?? 0;

        // Cek duplikasi di rombel yang sama
        $existing = Kurikulum::where('kelas', $kelasNama)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        if ($existing) {
            // Update jika sudah ada
            $existing->update([
                'guru_user_id' => $request->input('guru_user_id'),
                'alokasi_jam'  => (int) $request->input('alokasi_jam'),
                'keterangan'   => $request->input('keterangan'),
                'kategori'     => $kategori,
                'sub_kategori' => $subKategori,
                'urutan'       => $urutan,
            ]);
            return redirect()->back()->with('success', "Alokasi mata pelajaran untuk kelas {$kelasNama} berhasil diperbarui!");
        }

        $kurikulum = Kurikulum::create([
            'tahun_ajaran'      => $tahunAjaran,
            'semester'          => $semester,
            'jenjang'           => $jenjang,
            'jurusan'           => $jurusan,
            'jurusan_id'        => $jurusanModel?->id,
            'kelas'             => $kelasNama,
            'kelas_id'          => $kelasModel?->id,
            'mata_pelajaran_id' => $mapelId,
            'kategori'          => $kategori,
            'sub_kategori'      => $subKategori,
            'urutan'            => $urutan,
            'guru_user_id'      => $request->input('guru_user_id') ?: null,
            'alokasi_jam'       => (int) $request->input('alokasi_jam'),
            'keterangan'        => $request->input('keterangan'),
            'is_aktif'          => true,
        ]);

        // Buat placeholder di Master Jadwal Pelajaran
        \App\Models\JadwalPelajaran::create([
            'hari' => 'Belum diatur',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => (int) $request->input('alokasi_jam'),
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:00:00', // Dibiarkan kosong/default, akan disesuaikan saat plot hari
            'kelas' => $kelasNama,
            'mata_pelajaran_id' => $mapelId,
            'guru_user_id' => $request->input('guru_user_id') ?: null,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
        ]);

        return redirect()->back()->with('success', "Berhasil menambahkan mata pelajaran ke kurikulum kelas {$kelasNama} dan mem-plot jadwal kosong!");
    }

    /**
     * Update Pengampu & Alokasi Jam
     */
    public function update(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'kelas'             => 'required|string|max:50',
            'mata_pelajaran_id' => 'required',
            'guru_user_id'      => 'nullable|exists:users,id',
            'alokasi_jam'       => 'required|integer|min:1|max:24',
            'keterangan'        => 'nullable|string|max:255',
            'kategori'          => 'nullable|string|max:100',
            'sub_kategori'      => 'nullable|string|max:100',
        ]);

        $kelasNama = trim($request->input('kelas'));
        $mapelInput = $request->input('mata_pelajaran_id');

        // Deteksi Jenjang & Jurusan dari nama kelas
        $jenjang = 'X';
        if (str_starts_with($kelasNama, 'XII')) {
            $jenjang = 'XII';
        } elseif (str_starts_with($kelasNama, 'XI')) {
            $jenjang = 'XI';
        }

        $jurusan = 'TKJT';
        if (str_contains($kelasNama, 'TO')) {
            $jurusan = 'TO';
        } elseif (str_contains($kelasNama, 'AKL')) {
            $jurusan = 'AKL';
        }

        $kelasModel = Kelas::where('nama', $kelasNama)->first();
        $jurusanModel = Jurusan::where('kode', $jurusan)->first();

        // Cari atau buat mapel baru
        $mapelModel = MataPelajaran::find($mapelInput);
        if (!$mapelModel) {
            $mapelModel = MataPelajaran::firstOrCreate(
                ['nama' => $mapelInput],
                [
                    'kode' => strtoupper(substr(preg_replace('/[^A-Za-z0-9\-]/', '', $mapelInput), 0, 5)) . '-' . rand(100, 999),
                    'kategori' => 'A. KELOMPOK MATA PELAJARAN UMUM',
                    'is_aktif' => true,
                    'jam_per_minggu' => (int) $request->input('alokasi_jam')
                ]
            );
        }

        $kurikulum->update([
            'kelas'             => $kelasNama,
            'kelas_id'          => $kelasModel?->id,
            'jenjang'           => $jenjang,
            'jurusan'           => $jurusan,
            'jurusan_id'        => $jurusanModel?->id,
            'mata_pelajaran_id' => $mapelModel->id,
            'kategori'          => $request->filled('kategori') ? $request->input('kategori') : ($mapelModel->kategori ?? 'A. KELOMPOK MATA PELAJARAN UMUM'),
            'sub_kategori'      => $request->filled('sub_kategori') ? $request->input('sub_kategori') : $mapelModel->sub_kategori,
            'urutan'            => $mapelModel->urutan ?? 0,
            'guru_user_id'      => $request->input('guru_user_id') ?: null,
            'alokasi_jam'       => (int) $request->input('alokasi_jam'),
            'keterangan'        => $request->input('keterangan'),
        ]);

        return redirect()->back()->with('success', "Data kurikulum mata pelajaran berhasil diperbarui!");
    }

    /**
     * Hapus Satu Baris Kurikulum
     */
    public function destroy(Kurikulum $kurikulum)
    {
        $mapelId = $kurikulum->mata_pelajaran_id;
        $info = "{$kurikulum->mataPelajaran->nama} di {$kurikulum->kelas}";
        
        // Hapus SEMUA Kurikulum yang menggunakan mapel ini (mencegah data yatim piatu)
        Kurikulum::where('mata_pelajaran_id', $mapelId)->delete();
        
        // Hapus dari Master Mapel secara langsung (unconditional)
        \App\Models\MataPelajaran::where('id', $mapelId)->delete();

        return redirect()->back()->with('success', "Alokasi {$info} dan Master Mapel terkait berhasil dihapus sepenuhnya.");
    }

    /**
     * Bulk Delete Terpilih
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data kurikulum yang dipilih untuk dihapus.');
        }

        // Ambil ID mapel dari kurikulum yang akan dihapus
        $mapelIds = Kurikulum::whereIn('id', $ids)->pluck('mata_pelajaran_id')->unique();

        // Hapus SEMUA Kurikulum yang menggunakan mapel-mapel ini
        Kurikulum::whereIn('mata_pelajaran_id', $mapelIds)->delete();

        // Hapus dari Master Mapel secara langsung (unconditional)
        \App\Models\MataPelajaran::whereIn('id', $mapelIds)->delete();

        return redirect()->back()->with('success', "Berhasil menghapus data alokasi kurikulum beserta Master Mapel-nya.");
    }

    /**
     * Salin / Duplikasi Struktur Kurikulum dari Satu Rombel ke Rombel Lain
     */
    public function duplicate(Request $request)
    {
        $request->validate([
            'from_kelas' => 'required|string',
            'to_kelas'   => 'required|string|different:from_kelas',
        ]);

        $fromKelas = $request->input('from_kelas');
        $toKelas = $request->input('to_kelas');
        $salinGuru = $request->boolean('with_guru', true);

        $sourceItems = Kurikulum::where('kelas', $fromKelas)->where('is_aktif', true)->get();
        if ($sourceItems->isEmpty()) {
            return redirect()->back()->with('error', "Kelas sumber ({$fromKelas}) belum memiliki alokasi kurikulum.");
        }

        // Deteksi jenjang & jurusan kelas tujuan
        $jenjang = 'X';
        if (str_starts_with($toKelas, 'XII')) {
            $jenjang = 'XII';
        } elseif (str_starts_with($toKelas, 'XI')) {
            $jenjang = 'XI';
        }

        $jurusan = 'TKJT';
        if (str_contains($toKelas, 'TO')) {
            $jurusan = 'TO';
        } elseif (str_contains($toKelas, 'AKL')) {
            $jurusan = 'AKL';
        }

        $kelasModel = Kelas::where('nama', $toKelas)->first();
        $jurusanModel = Jurusan::where('kode', $jurusan)->first();
        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2025 - 2026');
        $semester = PengaturanSekolah::get('semester', 'Ganjil');

        $inserted = 0;
        foreach ($sourceItems as $item) {
            // Hindari duplikasi jika mapel sudah ada di to_kelas
            $exists = Kurikulum::where('kelas', $toKelas)
                ->where('mata_pelajaran_id', $item->mata_pelajaran_id)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->exists();

            if (!$exists) {
                Kurikulum::create([
                    'tahun_ajaran'      => $tahunAjaran,
                    'semester'          => $semester,
                    'jenjang'           => $jenjang,
                    'jurusan'           => $jurusan,
                    'jurusan_id'        => $jurusanModel?->id,
                    'kelas'             => $toKelas,
                    'kelas_id'          => $kelasModel?->id,
                    'mata_pelajaran_id' => $item->mata_pelajaran_id,
                    'kategori'          => $item->kategori,
                    'sub_kategori'      => $item->sub_kategori,
                    'urutan'            => $item->urutan,
                    'guru_user_id'      => $salinGuru ? $item->guru_user_id : null,
                    'alokasi_jam'       => $item->alokasi_jam,
                    'keterangan'        => $item->keterangan,
                    'is_aktif'          => true,
                ]);
                $inserted++;
            }
        }

        return redirect()->back()->with('success', "Berhasil menyalin {$inserted} mata pelajaran dari {$fromKelas} ke {$toKelas}!");
    }

    /**
     * Cetak Resmi Kurikulum & Pembagian Tugas Mengajar KBM
     */
    public function print(Request $request)
    {
        $jurusan = $request->query('jurusan', 'all');
        $jenjang = $request->query('jenjang', 'all');
        $kelas = $request->query('kelas', 'all');

        $settings = PengaturanSekolah::getAllSettings();

        $query = Kurikulum::with(['mataPelajaran', 'guru', 'kelasModel', 'jurusanModel'])
            ->where('is_aktif', true);

        if ($jurusan && $jurusan !== 'all') {
            $query->where('jurusan', $jurusan);
        }

        if ($jenjang && $jenjang !== 'all') {
            $query->where('jenjang', $jenjang);
        }

        if ($kelas && $kelas !== 'all') {
            $query->where('kelas', $kelas);
        }

        $kurikulums = $query->orderByRaw("CASE jenjang WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('kelas')
            ->orderByRaw("CASE COALESCE(kategori, 'A. KELOMPOK MATA PELAJARAN UMUM') WHEN 'A. KELOMPOK MATA PELAJARAN UMUM' THEN 1 WHEN 'B. KELOMPOK MATA PELAJARAN KEJURUAN' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE COALESCE(sub_kategori, '') WHEN 'Dasar-dasar Program Keahlian' THEN 1 WHEN 'Mata Pelajaran [Konsentrasi Keahlian]***' THEN 2 WHEN 'Mata Pelajaran Pilihan****' THEN 3 ELSE 4 END")
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $totalJp = $kurikulums->sum('alokasi_jam');
        $totalMapel = $kurikulums->count();
        $totalGuru = $kurikulums->whereNotNull('guru_user_id')->pluck('guru_user_id')->unique()->count();

        $kurikulumsByKelas = $kurikulums->groupBy('kelas');

        return view('admin.kurikulum.print', compact(
            'kurikulums',
            'kurikulumsByKelas',
            'jurusan',
            'jenjang',
            'kelas',
            'settings',
            'totalJp',
            'totalMapel',
            'totalGuru'
        ));
    }

    /**
     * Export Kurikulum ke Excel (XLSX) atau CSV
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        $jurusan = $request->query('jurusan', 'all');
        $jenjang = $request->query('jenjang', 'all');
        $kelas = $request->query('kelas', 'all');

        $query = Kurikulum::with(['mataPelajaran', 'guru'])
            ->where('is_aktif', true);

        if ($jurusan && $jurusan !== 'all') $query->where('jurusan', $jurusan);
        if ($jenjang && $jenjang !== 'all') $query->where('jenjang', $jenjang);
        if ($kelas && $kelas !== 'all') $query->where('kelas', $kelas);

        $items = $query->orderByRaw("CASE jenjang WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('kelas')
            ->orderByRaw("CASE COALESCE(kategori, 'A. KELOMPOK MATA PELAJARAN UMUM') WHEN 'A. KELOMPOK MATA PELAJARAN UMUM' THEN 1 WHEN 'B. KELOMPOK MATA PELAJARAN KEJURUAN' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE COALESCE(sub_kategori, '') WHEN 'Dasar-dasar Program Keahlian' THEN 1 WHEN 'Mata Pelajaran [Konsentrasi Keahlian]***' THEN 2 WHEN 'Mata Pelajaran Pilihan****' THEN 3 ELSE 4 END")
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $headers = [
            'No',
            'Tahun Ajaran',
            'Semester',
            'Jenjang',
            'Jurusan',
            'Kelas / Rombel',
            'Kode Mapel',
            'Nama Mata Pelajaran',
            'Kelompok Mapel',
            'Nama Guru Pengampu',
            'NIP Guru',
            'Alokasi Jam (JP/Minggu)',
            'Keterangan',
        ];

        $filename = 'Kurikulum_KBM_' . date('Ymd_His') . ($format === 'csv' ? '.csv' : '.xlsx');

        if ($format === 'csv') {
            return new StreamedResponse(function () use ($headers, $items) {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM agar Excel Windows membuka karakter dengan benar
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, $headers);

                $no = 1;
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $no++,
                        $item->tahun_ajaran,
                        $item->semester,
                        $item->jenjang,
                        $item->jurusan,
                        $item->kelas,
                        $item->mataPelajaran->kode ?? '',
                        $item->mataPelajaran->nama ?? '',
                        $item->mataPelajaran->kelompok ?? 'umum',
                        $item->guru->name ?? 'Belum Ditentukan',
                        $item->guru->nip ?? '-',
                        $item->alokasi_jam,
                        $item->keterangan ?? '',
                    ]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Format Excel (.xlsx)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }
        $sheet->getStyle('A1:' . chr(ord('A') + count($headers) - 1) . '1')->getFont()->setBold(true);

        // Data
        $rowNum = 2;
        $no = 1;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $item->tahun_ajaran);
            $sheet->setCellValue('C' . $rowNum, $item->semester);
            $sheet->setCellValue('D' . $rowNum, $item->jenjang);
            $sheet->setCellValue('E' . $rowNum, $item->jurusan);
            $sheet->setCellValue('F' . $rowNum, $item->kelas);
            $sheet->setCellValueExplicit('G' . $rowNum, $item->mataPelajaran->kode ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $rowNum, $item->mataPelajaran->nama ?? '');
            $sheet->setCellValue('I' . $rowNum, $item->mataPelajaran->kelompok ?? 'umum');
            $sheet->setCellValue('J' . $rowNum, $item->guru->name ?? 'Belum Ditentukan');
            $sheet->setCellValueExplicit('K' . $rowNum, $item->guru->nip ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L' . $rowNum, $item->alokasi_jam);
            $sheet->setCellValue('M' . $rowNum, $item->keterangan ?? '');
            $rowNum++;
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download Template CSV untuk Import Kurikulum
     */
    public function template()
    {
        $headers = [
            'Kelas',
            'Kode Mapel',
            'Nama Mapel',
            'Nama Atau Nip Guru',
            'Alokasi Jam',
            'Keterangan'
        ];

        $sampleRows = [
            ['X TKJT', 'INF', 'Informatika', 'Moh. Roghib, S.Kom', '4', 'Teori & Praktik Lab'],
            ['X TKJT', 'TKJT-DASAR', 'Dasar-dasar Keahlian TKJT', 'Sokid, ST, M.Kom', '6', 'Konsentrasi Jaringan'],
            ['X AKL', 'AKL-DASAR', 'Dasar-dasar Keahlian Akuntansi', 'Rizki Dwi Safitri, S.Pd', '6', 'Perbankan'],
            ['X TO', 'TO-DASAR', 'Dasar-dasar Keahlian Otomotif', 'Ismail Fahmi, ST', '6', 'Bengkel Otomotif'],
            ['XI TKJT', 'TKJT-JARKOM', 'Jaringan Komputer dan Telekomunikasi', 'Moh. Roghib, S.Kom', '6', 'Pecah 2 Jam & 4 Jam'],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Tulis Header
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
            $sheet->getStyleByColumnAndRow($colIndex, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
            $colIndex++;
        }

        // 2. Tulis Data Sampel
        $rowIndex = 2;
        foreach ($sampleRows as $row) {
            $colIndex = 1;
            foreach ($row as $cellValue) {
                // Konversi explisit ke string agar 0 di depan NIP tidak hilang, walau di sampel ini tidak ada
                $sheet->setCellValueExplicit([$colIndex, $rowIndex], $cellValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colIndex++;
            }
            $rowIndex++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template_import_kurikulum.xlsx';

        $headersResponse = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $headersResponse);
    }

    /**
     * Import Data Kurikulum dari CSV
     */
    /**
     * Import Data Kurikulum dari CSV / Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca berkas: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', '❌ Berkas kosong atau tidak dapat dibaca.');
        }

        // ── Peta alias kolom yang dikenali ────────────────────────────────
        $headerMap = [
            'kelas'       => ['kelas', 'rombel', 'nama_kelas', 'kelas tujuan'],
            'kode_mapel'  => ['kode_mapel', 'kode mapel', 'kode'],
            'nama_mapel'  => ['nama_mapel', 'nama mapel', 'mata pelajaran', 'nama mata pelajaran', 'mapel'],
            'guru'        => ['nama_atau_nip_guru', 'guru pengampu', 'guru', 'nama guru', 'nip guru', 'nip/nama guru', 'nama/nip guru'],
            'alokasi'     => ['alokasi_jam', 'alokasi jam', 'alokasi', 'jp', 'beban jp', 'jam'],
            'keterangan'  => ['keterangan', 'ket', 'catatan'],
        ];

        $rawHeader = array_map(fn($v) => strtolower(trim((string)($v ?? ''))), $rows[0]);
        $colMap = [];
        $usedIndices = [];

        // Pass 1: Exact matches
        foreach ($headerMap as $field => $aliases) {
            foreach ($rawHeader as $idx => $headerVal) {
                if (in_array($idx, $usedIndices)) continue;
                foreach ($aliases as $alias) {
                    if ($headerVal === strtolower($alias)) {
                        $colMap[$field] = $idx;
                        $usedIndices[] = $idx;
                        break 2;
                    }
                }
            }
        }

        // Pass 2: Partial matches
        foreach ($headerMap as $field => $aliases) {
            if (isset($colMap[$field])) continue;
            foreach ($rawHeader as $idx => $headerVal) {
                if (in_array($idx, $usedIndices) || empty($headerVal)) continue;
                foreach ($aliases as $alias) {
                    $alias = strtolower($alias);
                    if (strlen($alias) >= 3 && str_contains($headerVal, $alias)) {
                        $colMap[$field] = $idx;
                        $usedIndices[] = $idx;
                        break 2;
                    }
                }
            }
        }

        // Validasi kolom wajib: minimal kelas dan mapel (kode atau nama)
        if (!isset($colMap['kelas']) || (!isset($colMap['kode_mapel']) && !isset($colMap['nama_mapel']))) {
            $errors = ['❌ Kolom wajib kurikulum tidak ditemukan pada baris header file.'];
            $detected = array_filter(array_map('ucfirst', $rawHeader));
            $errors[] = 'Header yang terdeteksi: ' . (empty($detected) ? '(tidak ada)' : implode(' | ', $detected));
            if (!isset($colMap['kelas'])) {
                $errors[] = '❌ Kolom "Kelas" tidak ditemukan. Pastikan ada kolom "kelas" atau "rombel".';
            }
            if (!isset($colMap['kode_mapel']) && !isset($colMap['nama_mapel'])) {
                $errors[] = '❌ Kolom "Kode Mapel" atau "Nama Mapel" tidak ditemukan.';
            }
            $errors[] = '--- Format Template Kurikulum yang Diharapkan ---';
            $errors[] = 'kelas, kode_mapel, nama_mapel, nama_atau_nip_guru, alokasi_jam, keterangan';
            $errors[] = 'ℹ️ File Export Kurikulum juga dapat langsung diimport kembali secara otomatis.';

            return redirect()->back()->with('error', 'Import gagal karena format tabel/kolom tidak sesuai.')->with('import_errors', $errors);
        }

        if (count($rows) <= 1) {
            return redirect()->back()->with('error', '⚠️ Berkas hanya berisi baris header tanpa baris data.');
        }

        $tahunAjaran = PengaturanSekolah::get('tahun_pelajaran', '2025 - 2026');
        $semester = PengaturanSekolah::get('semester', 'Ganjil');

        $imported = 0;
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $row = $rows[$i];

            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $kelasNama = trim((string)($row[$colMap['kelas']] ?? ''));
            $kodeMapel = trim((string)($row[$colMap['kode_mapel'] ?? -1] ?? ''));
            $namaMapel = trim((string)($row[$colMap['nama_mapel'] ?? -1] ?? ''));
            $guruRef   = trim((string)($row[$colMap['guru'] ?? -1] ?? ''));
            $alokasi   = (int) trim((string)($row[$colMap['alokasi'] ?? -1] ?? '2'));
            $ket       = trim((string)($row[$colMap['keterangan'] ?? -1] ?? ''));

            if (empty($kelasNama)) {
                $errors[] = "⚠️ Baris {$rowNum}: Kolom Kelas tidak boleh kosong.";
                continue;
            }

            // Cari Mapel berdasarkan kode atau nama
            $mapel = null;
            if (!empty($kodeMapel)) {
                $mapel = MataPelajaran::where('kode', $kodeMapel)->first();
            }
            if (!$mapel && !empty($namaMapel)) {
                $mapel = MataPelajaran::where('nama', 'like', "%{$namaMapel}%")->first();
            }

            if (!$mapel) {
                // Auto create mapel jika tidak ada
                $namaBaru = !empty($namaMapel) ? $namaMapel : $kodeMapel;
                $kodeBaru = !empty($kodeMapel) ? $kodeMapel : strtoupper(substr(preg_replace('/[^A-Za-z0-9\-]/', '', $namaBaru), 0, 5)) . '-' . rand(100, 999);
                
                $mapel = MataPelajaran::create([
                    'nama' => $namaBaru,
                    'kode' => $kodeBaru,
                    'kategori' => 'A. KELOMPOK MATA PELAJARAN UMUM',
                    'is_aktif' => true,
                    'jam_per_minggu' => max(1, $alokasi)
                ]);
            }

            // Cari Guru berdasarkan NIP atau Nama
            $guru = null;
            if (!empty($guruRef)) {
                $guru = User::where('role', 'guru')
                    ->where(function ($q) use ($guruRef) {
                        $q->where('nip', $guruRef)
                          ->orWhere('name', 'like', "%{$guruRef}%");
                    })->first();
            }

            // Jenjang & Jurusan
            $jenjang = 'X';
            if (str_starts_with($kelasNama, 'XII')) $jenjang = 'XII';
            elseif (str_starts_with($kelasNama, 'XI')) $jenjang = 'XI';

            $jurusan = 'TKJT';
            if (str_contains($kelasNama, 'TO')) $jurusan = 'TO';
            elseif (str_contains($kelasNama, 'AKL')) $jurusan = 'AKL';

            $kelasModel = Kelas::where('nama', $kelasNama)->first();
            $jurusanModel = Jurusan::where('kode', $jurusan)->first();

            $kurikulum = Kurikulum::updateOrCreate(
                [
                    'tahun_ajaran'      => $tahunAjaran,
                    'semester'          => $semester,
                    'kelas'             => $kelasNama,
                    'mata_pelajaran_id' => $mapel->id,
                ],
                [
                    'jenjang'      => $jenjang,
                    'jurusan'      => $jurusan,
                    'jurusan_id'   => $jurusanModel?->id,
                    'kelas_id'     => $kelasModel?->id,
                    'guru_user_id' => $guru?->id,
                    'alokasi_jam'  => max(1, $alokasi),
                    'keterangan'   => $ket ?: null,
                    'is_aktif'     => true,
                ]
            );

            // Cek apakah sudah ada placeholder jadwal untuk kurikulum ini
            $jadwalExist = \App\Models\JadwalPelajaran::where('kelas', $kelasNama)
                ->where('mata_pelajaran_id', $mapel->id)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->exists();

            if (!$jadwalExist) {
                \App\Models\JadwalPelajaran::create([
                    'hari' => 'Belum diatur',
                    'jam_ke_mulai' => 1,
                    'jam_ke_selesai' => max(1, $alokasi),
                    'jam_mulai' => '07:00:00',
                    'jam_selesai' => '07:00:00',
                    'kelas' => $kelasNama,
                    'mata_pelajaran_id' => $mapel->id,
                    'guru_user_id' => $guru?->id,
                    'tahun_ajaran' => $tahunAjaran,
                    'semester' => $semester,
                ]);
            }

            $imported++;
        }

        if ($imported === 0 && !empty($errors)) {
            return redirect()->back()->with('error', 'Import kurikulum gagal. Mohon periksa kesalahan berikut:')->with('import_errors', $errors);
        }

        $msg = "Berhasil mengimpor {$imported} alokasi kurikulum mata pelajaran.";
        if (!empty($errors)) {
            return redirect()->back()->with('warning', "{$msg} Namun ada " . count($errors) . " baris data yang dilewati.")->with('import_errors', $errors);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Endpoint JSON API: Ambil Mata Pelajaran Kurikulum berdasarkan Kelas
     * (Digunakan pada modal input Jadwal Pelajaran agar otomatis terisi)
     */
    public function getByKelas(Request $request)
    {
        $kelas = $request->query('kelas');
        if (empty($kelas)) {
            return response()->json([]);
        }

        $items = Kurikulum::with(['mataPelajaran', 'guru'])
            ->where('kelas', $kelas)
            ->where('is_aktif', true)
            ->get();

        $data = $items->map(function ($k) {
            return [
                'id'                => $k->id,
                'mata_pelajaran_id' => $k->mata_pelajaran_id,
                'mapel_kode'        => $k->mataPelajaran->kode ?? '',
                'mapel_nama'        => $k->mataPelajaran->nama ?? 'Mapel #' . $k->mata_pelajaran_id,
                'guru_user_id'      => $k->guru_user_id,
                'guru_nama'         => $k->guru->name ?? 'Belum Ditentukan',
                'guru_jabatan'      => $k->guru->jabatan_utama ?? '',
                'alokasi_jam'       => $k->alokasi_jam,
                'terjadwal_jp'      => $k->terjadwal_jp,
                'sisa_jp'           => $k->sisa_jp,
                'status_jadwal'     => $k->status_jadwal,
            ];
        });

        return response()->json($data);
    }
}
