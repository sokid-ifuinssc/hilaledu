<?php

namespace App\Http\Controllers;

use App\Models\DokumenSertifikatSiswa;
use App\Models\JenisSertifikat;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\User;
use App\Services\SertifikatPdfGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DokumenSertifikatController extends Controller
{
    public const VALID_KATEGORIS = ['kepesantrenan', 'kewirausahaan', 'skl', 'ijazah'];

    /**
     * Pastikan tabel tersedia (auto-heal jika migrasi belum dijalankan di server)
     */
    protected function ensureSchema(): void
    {
        try {
            if (!Schema::hasTable('jenis_sertifikats')) {
                Schema::create('jenis_sertifikats', function (Blueprint $table) {
                    $table->id();
                    $table->string('kategori', 50)->index();
                    $table->string('nama_sertifikat');
                    $table->string('deskripsi')->nullable();
                    $table->boolean('is_aktif')->default(true);
                    $table->timestamps();
                });

                // Seed contoh nama sertifikat awal agar langsung siap pakai
                $defaults = [
                    ['kategori' => 'kepesantrenan', 'nama_sertifikat' => 'Sertifikat Khatam Juz', 'deskripsi' => 'Sertifikat kelulusan khatam Al-Qur\'an'],
                    ['kategori' => 'kepesantrenan', 'nama_sertifikat' => 'Sertifikat Keahlian Tahlil', 'deskripsi' => 'Sertifikat kecakapan memimpin tahlil & doa'],
                    ['kategori' => 'kewirausahaan', 'nama_sertifikat' => 'Sertifikat Kompetensi Kewirausahaan', 'deskripsi' => 'Sertifikat praktik kewirausahaan siswa'],
                    ['kategori' => 'kewirausahaan', 'nama_sertifikat' => 'Sertifikat Produk Kreatif', 'deskripsi' => 'Sertifikat karya inovasi & produk kreatif'],
                ];
                foreach ($defaults as $d) {
                    JenisSertifikat::create($d);
                }
            }

            if (!Schema::hasTable('dokumen_sertifikat_siswas')) {
                Schema::create('dokumen_sertifikat_siswas', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('siswa_id')->index();
                    $table->string('kategori', 50)->index();
                    $table->unsignedBigInteger('jenis_sertifikat_id')->nullable()->index();
                    $table->string('nama_dokumen');
                    $table->string('nomor_dokumen')->nullable();
                    $table->string('file_path');
                    $table->string('file_name')->nullable();
                    $table->unsignedBigInteger('file_size')->nullable();
                    $table->text('keterangan')->nullable();
                    $table->unsignedBigInteger('uploaded_by')->nullable();
                    $table->boolean('is_generated')->default(false);
                    $table->string('nama_kegiatan')->nullable();
                    $table->text('deskripsi_kegiatan')->nullable();
                    $table->string('predikat', 50)->nullable();
                    $table->string('tempat_terbit', 100)->nullable();
                    $table->string('tanggal_terbit', 100)->nullable();
                    $table->string('pejabat_1_jabatan', 150)->nullable();
                    $table->string('pejabat_1_nama', 150)->nullable();
                    $table->string('pejabat_1_nip', 100)->nullable();
                    $table->string('pejabat_2_jabatan', 150)->nullable();
                    $table->string('pejabat_2_nama', 150)->nullable();
                    $table->string('pejabat_2_nip', 100)->nullable();
                    $table->timestamps();
                });
            } else {
                $newCols = [
                    'is_generated'       => fn(Blueprint $t) => $t->boolean('is_generated')->default(false),
                    'nama_kegiatan'      => fn(Blueprint $t) => $t->string('nama_kegiatan')->nullable(),
                    'deskripsi_kegiatan' => fn(Blueprint $t) => $t->text('deskripsi_kegiatan')->nullable(),
                    'predikat'           => fn(Blueprint $t) => $t->string('predikat', 50)->nullable(),
                    'tempat_terbit'      => fn(Blueprint $t) => $t->string('tempat_terbit', 100)->nullable(),
                    'tanggal_terbit'     => fn(Blueprint $t) => $t->string('tanggal_terbit', 100)->nullable(),
                    'pejabat_1_jabatan'  => fn(Blueprint $t) => $t->string('pejabat_1_jabatan', 150)->nullable(),
                    'pejabat_1_nama'     => fn(Blueprint $t) => $t->string('pejabat_1_nama', 150)->nullable(),
                    'pejabat_1_nip'      => fn(Blueprint $t) => $t->string('pejabat_1_nip', 100)->nullable(),
                    'pejabat_2_jabatan'  => fn(Blueprint $t) => $t->string('pejabat_2_jabatan', 150)->nullable(),
                    'pejabat_2_nama'     => fn(Blueprint $t) => $t->string('pejabat_2_nama', 150)->nullable(),
                    'pejabat_2_nip'      => fn(Blueprint $t) => $t->string('pejabat_2_nip', 100)->nullable(),
                ];
                foreach ($newCols as $col => $callback) {
                    if (!Schema::hasColumn('dokumen_sertifikat_siswas', $col)) {
                        Schema::table('dokumen_sertifikat_siswas', $callback);
                    }
                }
            }

            if (Schema::hasTable('users')) {
                if (!Schema::hasColumn('users', 'akses_dokumen_dibatasi')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->boolean('akses_dokumen_dibatasi')->default(false);
                    });
                }
                if (!Schema::hasColumn('users', 'catatan_pembatasan_dokumen')) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('catatan_pembatasan_dokumen', 500)->nullable();
                    });
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika sedang koneksi read-only
        }
    }

    /**
     * Daftar kategori dokumen & sertifikat yang berhak dikelola oleh user:
     * - Superadmin: seluruh kategori (kepesantrenan, kewirausahaan, skl, ijazah)
     * - Admin Sertifikat Kepesantrenan (sertifikat_kepesantrenan): kepesantrenan
     * - Admin Sertifikat Kewirausahaan (sertifikat_kewirausahaan): kewirausahaan
     * - Admin SKL & Ijazah (skl_ijazah): skl dan ijazah (digabung bareng)
     */
    public static function allowedKategoris(?User $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->isSuperAdmin() || $user->hasAdminRole('dokumen')) {
            return self::VALID_KATEGORIS;
        }

        $allowed = [];
        if ($user->hasAdminRole('sertifikat_kepesantrenan')) {
            $allowed[] = 'kepesantrenan';
        }
        if ($user->hasAdminRole('sertifikat_kewirausahaan')) {
            $allowed[] = 'kewirausahaan';
        }
        if ($user->hasAdminRole('skl_ijazah')) {
            $allowed[] = 'skl';
            $allowed[] = 'ijazah';
        }

        return array_values(array_unique($allowed));
    }

    /**
     * Cek apakah user memiliki hak kelola dokumen & sertifikat (minimal 1 kategori)
     */
    public static function canManage(?User $user): bool
    {
        return count(self::allowedKategoris($user)) > 0;
    }

    /**
     * Cek apakah user berhak mengelola kategori spesifik
     */
    public static function canManageKategori(?User $user, string $kategori): bool
    {
        return in_array($kategori, self::allowedKategoris($user), true);
    }

    protected function authorizeAdmin(?string $kategori = null): void
    {
        $user = Auth::user();
        if (!self::canManage($user)) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola Dokumen & Sertifikat.');
        }
        if ($kategori !== null && !self::canManageKategori($user, $kategori)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengelola kategori sertifikat/dokumen ini.');
        }
    }

    /**
     * Halaman Pengelolaan Dokumen & Sertifikat (Superadmin & Admin sesuai unit penugasannya)
     */
    public function adminIndex(Request $request, ?string $kategori = null)
    {
        $this->authorizeAdmin();
        $this->ensureSchema();

        $allowedKategoris = self::allowedKategoris(Auth::user());

        // Jika kategori tidak diisi atau admin tidak memiliki akses ke kategori tsb, arahkan ke kategori pertama yang diizinkan
        if (!$kategori || !in_array($kategori, self::VALID_KATEGORIS, true) || !in_array($kategori, $allowedKategoris, true)) {
            $kategori = $allowedKategoris[0];
        }

        $config = DokumenSertifikatSiswa::getKategoriConfig($kategori);
        $kelasId = $request->query('kelas_id');
        $search = trim((string) $request->query('search', ''));
        $statusFilter = $request->query('status_upload');
        $aksesFilter = $request->query('status_akses');
        $jenisFilter = $request->query('jenis_id');

        // Untuk SKL dan Ijazah: khusus tampilkan hanya Kelas XII saja agar data siswa tidak menumpuk
        $kelasesQuery = Kelas::orderBy('nama_kelas');
        if (in_array($kategori, ['skl', 'ijazah'], true)) {
            $kelasesQuery->where(function ($q) {
                $q->whereIn('tingkat', ['XII', '12', 12])
                  ->orWhere('nama_kelas', 'like', 'XII%')
                  ->orWhere('nama_kelas', 'like', '12%')
                  ->orWhere('nama', 'like', 'XII%')
                  ->orWhere('nama', 'like', '12%');
            });
        }
        $kelases = $kelasesQuery->get();

        // Daftar master nama sertifikat (khusus Kepesantrenan & Kewirausahaan)
        $jenisSertifikats = $config['is_multiple']
            ? JenisSertifikat::where('kategori', $kategori)
                ->withCount('dokumenSiswas')
                ->orderBy('nama_sertifikat')
                ->get()
            : collect();

        // Query siswa
        $siswaQuery = User::where('role', 'siswa')
            ->where('is_active', true)
            ->with('kelas')
            ->orderBy('kelas_id')
            ->orderBy('name');

        if (in_array($kategori, ['skl', 'ijazah'], true)) {
            $kelasXiiIds = $kelases->pluck('id')->toArray();
            $siswaQuery->whereIn('kelas_id', $kelasXiiIds);
        }

        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }

        if ($search !== '') {
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $allFilteredSiswas = $siswaQuery->get();
        $siswaIds = $allFilteredSiswas->pluck('id');

        // Ambil seluruh dokumen kategori ini untuk siswa-siswa tersebut
        $dokumenQuery = DokumenSertifikatSiswa::where('kategori', $kategori)
            ->whereIn('siswa_id', $siswaIds)
            ->with(['jenisSertifikat', 'uploader'])
            ->orderBy('created_at', 'desc');

        if ($jenisFilter) {
            $dokumenQuery->where('jenis_sertifikat_id', $jenisFilter);
        }

        $dokumensBySiswa = $dokumenQuery->get()->groupBy('siswa_id');

        // Hitung statistik sebelum filter status_upload & status_akses
        $totalSiswa = $allFilteredSiswas->count();
        $siswaSudahUpload = $allFilteredSiswas->filter(fn($s) => $dokumensBySiswa->has($s->id))->count();
        $siswaBelumUpload = $totalSiswa - $siswaSudahUpload;
        $siswaAksesDibatasi = $allFilteredSiswas->filter(fn($s) => (bool) $s->akses_dokumen_dibatasi)->count();
        $totalFilePdf = $dokumensBySiswa->flatten(1)->count();

        // Terapkan filter status_upload jika dipilih
        if ($statusFilter === 'sudah') {
            $allFilteredSiswas = $allFilteredSiswas->filter(fn($s) => $dokumensBySiswa->has($s->id))->values();
        } elseif ($statusFilter === 'belum') {
            $allFilteredSiswas = $allFilteredSiswas->filter(fn($s) => !$dokumensBySiswa->has($s->id))->values();
        }

        // Terapkan filter status_akses (administrasi) jika dipilih
        if ($aksesFilter === 'diizinkan') {
            $allFilteredSiswas = $allFilteredSiswas->filter(fn($s) => !(bool) $s->akses_dokumen_dibatasi)->values();
        } elseif ($aksesFilter === 'dibatasi') {
            $allFilteredSiswas = $allFilteredSiswas->filter(fn($s) => (bool) $s->akses_dokumen_dibatasi)->values();
        }

        // Ambil default Kepala Sekolah & Daftar Pejabat/Pegawai dari database untuk form Generator Sertifikat
        $namaSekolahDefault = 'SMK Plus Al Hilal';
        $defaultKepsekId = null;
        $defaultKepsekJabatan = 'Kepala SMK Plus Al Hilal';
        $defaultKepsekNama = '';
        $defaultKepsekNip = '';
        try {
            $settingSekolah = PengaturanSekolah::first();
            if ($settingSekolah && !empty($settingSekolah->nama_sekolah)) {
                $namaSekolahDefault = $settingSekolah->nama_sekolah;
            }
            if ($settingSekolah && $settingSekolah->kepala_sekolah_id) {
                $kepsekUser = User::find($settingSekolah->kepala_sekolah_id);
                if ($kepsekUser) {
                    $defaultKepsekId = $kepsekUser->id;
                    $defaultKepsekNama = $kepsekUser->name;
                    $rawNip = trim((string) $kepsekUser->nip);
                    $defaultKepsekNip = $rawNip !== ''
                        ? (preg_match('/^(NIP|NIY|NUPTK)/i', $rawNip) ? $rawNip : 'NIP. ' . $rawNip)
                        : '';
                }
            }
        } catch (\Throwable $e) {}

        // Susun daftar pejabat/guru/tendik dari database agar Jabatan, Nama, dan NIP bisa dipilih otomatis
        $daftarPejabat = [];
        try {
            $pegawaiUsers = User::whereIn('role', ['guru', 'tendik', 'admin', 'superadmin'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            foreach ($pegawaiUsers as $pUser) {
                $rawNip = trim((string) ($pUser->nip ?? ''));
                $formattedNip = $rawNip !== ''
                    ? (preg_match('/^(NIP|NIY|NUPTK)/i', $rawNip) ? $rawNip : 'NIP. ' . $rawNip)
                    : '';

                $jabatanOtomatis = '';
                if ($defaultKepsekId && (int) $pUser->id === (int) $defaultKepsekId) {
                    $jabatanOtomatis = $defaultKepsekJabatan;
                } elseif (!empty($pUser->jabatan_utama)) {
                    $jabatanOtomatis = trim((string) $pUser->jabatan_utama);
                } else {
                    $tugasTambahan = $pUser->tugas_tambahan;
                    if (is_array($tugasTambahan) && !empty($tugasTambahan)) {
                        $firstTugas = trim((string) $tugasTambahan[0]);
                        if (strtolower($firstTugas) === 'kepala sekolah') {
                            $jabatanOtomatis = $defaultKepsekJabatan;
                        } else {
                            $jabatanOtomatis = $firstTugas;
                        }
                    }
                }

                if ($jabatanOtomatis === '') {
                    $units = is_array($pUser->admin_units) ? $pUser->admin_units : [];
                    if (in_array('sertifikat_kepesantrenan', $units, true)) {
                        $jabatanOtomatis = 'Ketua Lembaga Kepesantrenan';
                    } elseif (in_array('sertifikat_kewirausahaan', $units, true)) {
                        $jabatanOtomatis = 'Ketua Lembaga Kewirausahaan';
                    } elseif ($pUser->role === 'tendik') {
                        $jabatanOtomatis = 'Kepala Tata Usaha / Administrasi';
                    } else {
                        $jabatanOtomatis = $kategori === 'kepesantrenan'
                            ? 'Pembina Lembaga Kepesantrenan'
                            : 'Pembina Lembaga Kewirausahaan';
                    }
                }

                $daftarPejabat[] = [
                    'id'         => (string) $pUser->id,
                    'nama'       => $pUser->name,
                    'nip'        => $formattedNip,
                    'jabatan'    => $jabatanOtomatis,
                    'is_kepsek'  => $defaultKepsekId && (int) $pUser->id === (int) $defaultKepsekId,
                    'label'      => $pUser->name . ' — ' . $jabatanOtomatis . ($formattedNip ? " ({$formattedNip})" : ''),
                ];
            }

            // Urutkan agar Kepala Sekolah berada paling atas di dropdown
            usort($daftarPejabat, function ($a, $b) {
                if ($a['is_kepsek'] !== $b['is_kepsek']) {
                    return $a['is_kepsek'] ? -1 : 1;
                }
                return strcasecmp($a['nama'], $b['nama']);
            });
        } catch (\Throwable $e) {}

        return view('admin.dokumen_sertifikat.index', compact(
            'kategori',
            'allowedKategoris',
            'config',
            'kelases',
            'kelasId',
            'search',
            'statusFilter',
            'aksesFilter',
            'jenisFilter',
            'jenisSertifikats',
            'allFilteredSiswas',
            'dokumensBySiswa',
            'totalSiswa',
            'siswaSudahUpload',
            'siswaBelumUpload',
            'siswaAksesDibatasi',
            'totalFilePdf',
            'namaSekolahDefault',
            'defaultKepsekId',
            'defaultKepsekJabatan',
            'defaultKepsekNama',
            'defaultKepsekNip',
            'daftarPejabat'
        ));
    }

    /**
     * Tambah Master Nama Sertifikat Baru (Kepesantrenan / Kewirausahaan)
     */
    public function storeJenisSertifikat(Request $request, string $kategori)
    {
        $this->authorizeAdmin($kategori);
        $this->ensureSchema();

        if (!in_array($kategori, ['kepesantrenan', 'kewirausahaan'], true)) {
            abort(400);
        }

        $request->validate([
            'nama_sertifikat' => 'required|string|max:200',
            'deskripsi'       => 'nullable|string|max:255',
        ]);

        $nama = trim($request->nama_sertifikat);

        $exists = JenisSertifikat::where('kategori', $kategori)
            ->whereRaw('LOWER(nama_sertifikat) = ?', [strtolower($nama)])
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', "Nama sertifikat \"{$nama}\" sudah ada pada daftar.");
        }

        JenisSertifikat::create([
            'kategori'        => $kategori,
            'nama_sertifikat' => $nama,
            'deskripsi'       => $request->deskripsi,
            'is_aktif'        => true,
        ]);

        return redirect()->back()->with('success', "Nama sertifikat \"{$nama}\" berhasil ditambahkan! Sekarang Anda dapat mengunggah file PDF sertifikat ini untuk siswa.");
    }

    /**
     * Edit Master Nama Sertifikat
     */
    public function updateJenisSertifikat(Request $request, JenisSertifikat $jenis)
    {
        $this->authorizeAdmin($jenis->kategori);
        $this->ensureSchema();

        $request->validate([
            'nama_sertifikat' => 'required|string|max:200',
            'deskripsi'       => 'nullable|string|max:255',
        ]);

        $namaBaru = trim($request->nama_sertifikat);
        $jenis->update([
            'nama_sertifikat' => $namaBaru,
            'deskripsi'       => $request->deskripsi,
        ]);

        // Sinkronkan nama dokumen pada sertifikat siswa yang terhubung
        DokumenSertifikatSiswa::where('jenis_sertifikat_id', $jenis->id)
            ->update(['nama_dokumen' => $namaBaru]);

        return redirect()->back()->with('success', "Nama sertifikat berhasil diperbarui menjadi \"{$namaBaru}\".");
    }

    /**
     * Hapus Master Nama Sertifikat
     */
    public function destroyJenisSertifikat(JenisSertifikat $jenis)
    {
        $this->authorizeAdmin($jenis->kategori);
        $this->ensureSchema();

        $nama = $jenis->nama_sertifikat;
        $jenis->delete();

        return redirect()->back()->with('success', "Nama sertifikat \"{$nama}\" berhasil dihapus dari daftar master.");
    }

    /**
     * Upload Dokumen / Sertifikat PDF untuk Siswa
     */
    public function storeDokumen(Request $request, string $kategori)
    {
        $this->authorizeAdmin($kategori);
        $this->ensureSchema();

        if (!in_array($kategori, self::VALID_KATEGORIS, true)) {
            abort(400);
        }

        $config = DokumenSertifikatSiswa::getKategoriConfig($kategori);

        $rules = [
            'siswa_id'      => 'required|exists:users,id',
            'file_pdf'      => 'required|file|mimes:pdf|max:15360', // Maks 15 MB PDF
            'nomor_dokumen' => 'nullable|string|max:120',
            'keterangan'    => 'nullable|string|max:500',
        ];

        if ($config['is_multiple']) {
            $rules['jenis_sertifikat_id']  = 'nullable|exists:jenis_sertifikats,id';
            $rules['nama_sertifikat_baru'] = 'nullable|string|max:200';
        }

        $request->validate($rules, [
            'file_pdf.required' => 'Pilih file PDF yang akan diunggah.',
            'file_pdf.mimes'    => 'Format dokumen wajib berupa file PDF (.pdf).',
            'file_pdf.max'      => 'Ukuran file PDF maksimal 15 MB.',
        ]);

        $siswa = User::findOrFail($request->siswa_id);
        $file = $request->file('file_pdf');

        $jenisId = null;
        $namaDokumen = $config['title'];

        if ($config['is_multiple']) {
            // Jika admin mengetik nama sertifikat baru saat upload, simpan otomatis ke master JenisSertifikat
            if ($request->filled('nama_sertifikat_baru')) {
                $namaBaru = trim($request->nama_sertifikat_baru);
                $jenisObj = JenisSertifikat::firstOrCreate(
                    ['kategori' => $kategori, 'nama_sertifikat' => $namaBaru],
                    ['is_aktif' => true]
                );
                $jenisId = $jenisObj->id;
                $namaDokumen = $jenisObj->nama_sertifikat;
            } elseif ($request->filled('jenis_sertifikat_id')) {
                $jenisObj = JenisSertifikat::findOrFail($request->jenis_sertifikat_id);
                $jenisId = $jenisObj->id;
                $namaDokumen = $jenisObj->nama_sertifikat;
            } else {
                return redirect()->back()->with('error', 'Silakan pilih Nama Sertifikat terlebih dahulu atau buat nama sertifikat baru.');
            }
        } else {
            // Untuk SKL & Ijazah: hanya boleh 1 file per siswa (replace jika sudah ada)
            $existing = DokumenSertifikatSiswa::where('siswa_id', $siswa->id)
                ->where('kategori', $kategori)
                ->get();

            foreach ($existing as $oldDoc) {
                if ($oldDoc->file_path && Storage::disk('public')->exists($oldDoc->file_path)) {
                    Storage::disk('public')->delete($oldDoc->file_path);
                }
                $oldDoc->delete();
            }

            $namaDokumen = $kategori === 'skl' ? 'Surat Keterangan Lulus (SKL)' : 'Ijazah Kelulusan';
        }

        $safeSlug = Str::slug($namaDokumen) ?: $kategori;
        $safeSiswa = Str::slug($siswa->name) ?: 'siswa';
        $filename = "{$safeSlug}_{$safeSiswa}_{$siswa->id}_" . time() . '.pdf';
        $storedPath = $file->storeAs("dokumen_sertifikat/{$kategori}", $filename, 'public');

        DokumenSertifikatSiswa::create([
            'siswa_id'            => $siswa->id,
            'kategori'            => $kategori,
            'jenis_sertifikat_id' => $jenisId,
            'nama_dokumen'        => $namaDokumen,
            'nomor_dokumen'       => $request->nomor_dokumen,
            'file_path'           => $storedPath,
            'file_name'           => $file->getClientOriginalName(),
            'file_size'           => $file->getSize(),
            'keterangan'          => $request->keterangan,
            'uploaded_by'         => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Dokumen PDF \"{$namaDokumen}\" untuk siswa {$siswa->name} berhasil diunggah!");
    }

    /**
     * Hapus Dokumen / Sertifikat Siswa
     */
    public function destroyDokumen(DokumenSertifikatSiswa $dokumen)
    {
        $this->authorizeAdmin($dokumen->kategori);
        $this->ensureSchema();

        $nama = $dokumen->nama_dokumen;
        $siswaName = $dokumen->siswa->name ?? 'Siswa';

        if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
            Storage::disk('public')->delete($dokumen->file_path);
        }

        $dokumen->delete();

        return redirect()->back()->with('success', "Dokumen \"{$nama}\" milik {$siswaName} berhasil dihapus.");
    }

    /**
     * Pengaturan Akses Dokumen & Sertifikat Per Siswa (Pembatasan Administrasi)
     */
    public function toggleAksesSiswa(Request $request, User $siswa)
    {
        $this->authorizeAdmin();
        $this->ensureSchema();

        $request->validate([
            'akses_dokumen_dibatasi'     => 'required|boolean',
            'catatan_pembatasan_dokumen' => 'nullable|string|max:500',
        ]);

        $dibatasi = (bool) $request->boolean('akses_dokumen_dibatasi');
        $catatan = $dibatasi
            ? (trim((string) $request->input('catatan_pembatasan_dokumen')) ?: 'Administrasi sekolah belum diselesaikan. Silakan hubungi bagian Tata Usaha (TU) / Administrasi.')
            : null;

        $siswa->update([
            'akses_dokumen_dibatasi'     => $dibatasi,
            'catatan_pembatasan_dokumen' => $catatan,
        ]);

        if ($dibatasi) {
            return redirect()->back()->with('success', "Akses halaman Dokumen & Sertifikat untuk siswa {$siswa->name} telah DIBATASI (Terkunci karena administrasi).");
        }

        return redirect()->back()->with('success', "Akses halaman Dokumen & Sertifikat untuk siswa {$siswa->name} telah DIBUKA kembali.");
    }

    /**
     * Pengaturan Akses Dokumen & Sertifikat Secara Massal (Per Kelas atau Semua Siswa)
     */
    public function bulkAksesSiswa(Request $request)
    {
        $this->authorizeAdmin();
        $this->ensureSchema();

        $request->validate([
            'action'                     => 'required|in:batasi,buka',
            'kelas_id'                   => 'nullable|exists:kelas,id',
            'catatan_pembatasan_dokumen' => 'nullable|string|max:500',
        ]);

        $dibatasi = $request->input('action') === 'batasi';
        $catatan = $dibatasi
            ? (trim((string) $request->input('catatan_pembatasan_dokumen')) ?: 'Administrasi sekolah belum diselesaikan. Silakan hubungi bagian Tata Usaha (TU) / Administrasi.')
            : null;

        $query = User::where('role', 'siswa')->where('is_active', true);
        $targetLabel = 'seluruh siswa';

        if ($request->filled('kelas_id')) {
            $kelas = Kelas::find($request->kelas_id);
            $query->where('kelas_id', $request->kelas_id);
            $targetLabel = 'siswa Kelas ' . ($kelas->nama_kelas ?? '');
        }

        $count = $query->update([
            'akses_dokumen_dibatasi'     => $dibatasi,
            'catatan_pembatasan_dokumen' => $catatan,
        ]);

        if ($dibatasi) {
            return redirect()->back()->with('success', "Akses halaman Dokumen & Sertifikat berhasil DIBATASI untuk {$count} {$targetLabel}.");
        }

        return redirect()->back()->with('success', "Akses halaman Dokumen & Sertifikat berhasil DIBUKA kembali untuk {$count} {$targetLabel}.");
    }

    /**
     * Halaman Siswa: Melihat & Mengunduh Dokumen dan Sertifikat Milik Sendiri
     */
    public function siswaIndex(Request $request, string $kategori = 'kepesantrenan')
    {
        $this->ensureSchema();
        $user = Auth::user();

        if (!in_array($kategori, self::VALID_KATEGORIS, true)) {
            $kategori = 'kepesantrenan';
        }

        $config = DokumenSertifikatSiswa::getKategoriConfig($kategori);
        $isAksesDibatasi = (bool) ($user->akses_dokumen_dibatasi ?? false);
        $catatanPembatasan = $user->catatan_pembatasan_dokumen
            ?: 'Masih terdapat administrasi sekolah yang belum diselesaikan. Silakan hubungi bagian Administrasi / Tata Usaha (TU) sekolah untuk membuka akses sertifikat dan dokumen kelulusan Anda.';

        // Jika akses siswa sedang dibatasi karena masalah administrasi, sembunyikan seluruh dokumen sertifikat
        if ($isAksesDibatasi) {
            $countsByKategori = [
                'kepesantrenan' => 0,
                'kewirausahaan' => 0,
                'skl'           => 0,
                'ijazah'        => 0,
            ];
            $dokumens = collect();

            return view('siswa.dokumen_sertifikat.index', compact(
                'user',
                'kategori',
                'config',
                'dokumens',
                'countsByKategori',
                'isAksesDibatasi',
                'catatanPembatasan'
            ));
        }

        // Ambil seluruh dokumen milik siswa yang sedang login
        $allMyDocs = DokumenSertifikatSiswa::where('siswa_id', $user->id)
            ->with(['jenisSertifikat', 'uploader'])
            ->orderBy('created_at', 'desc')
            ->get();

        $countsByKategori = [
            'kepesantrenan' => $allMyDocs->where('kategori', 'kepesantrenan')->count(),
            'kewirausahaan' => $allMyDocs->where('kategori', 'kewirausahaan')->count(),
            'skl'           => $allMyDocs->where('kategori', 'skl')->count(),
            'ijazah'        => $allMyDocs->where('kategori', 'ijazah')->count(),
        ];

        $dokumens = $allMyDocs->where('kategori', $kategori)->values();

        return view('siswa.dokumen_sertifikat.index', compact(
            'user',
            'kategori',
            'config',
            'dokumens',
            'countsByKategori',
            'isAksesDibatasi',
            'catatanPembatasan'
        ));
    }

    /**
     * Stream / Preview File PDF di Browser (Aman dengan Otorisasi)
     */
    public function preview(DokumenSertifikatSiswa $dokumen)
    {
        $user = Auth::user();
        if (!self::canManage($user)) {
            if ((int) $dokumen->siswa_id !== (int) $user->id) {
                abort(403, 'Anda hanya dapat melihat dokumen milik Anda sendiri.');
            }
            if ((bool) ($user->akses_dokumen_dibatasi ?? false)) {
                abort(403, 'Akses dokumen dan sertifikat Anda sedang dibatasi karena administrasi belum selesai.');
            }
        }

        if (!$dokumen->file_path || !Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File PDF tidak ditemukan di server.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);
        $downloadName = ($dokumen->file_name ?: Str::slug($dokumen->nama_dokumen) . '.pdf');

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($downloadName) . '"',
        ]);
    }

    /**
     * Download File PDF (Aman dengan Otorisasi)
     */
    public function download(DokumenSertifikatSiswa $dokumen)
    {
        $user = Auth::user();
        if (!self::canManage($user)) {
            if ((int) $dokumen->siswa_id !== (int) $user->id) {
                abort(403, 'Anda hanya dapat mengunduh dokumen milik Anda sendiri.');
            }
            if ((bool) ($user->akses_dokumen_dibatasi ?? false)) {
                abort(403, 'Akses dokumen dan sertifikat Anda sedang dibatasi karena administrasi belum selesai.');
            }
        }

        if (!$dokumen->file_path || !Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File PDF tidak ditemukan di server.');
        }

        $siswaName = Str::slug($dokumen->siswa->name ?? 'siswa');
        $docSlug = Str::slug($dokumen->nama_dokumen ?: $dokumen->kategori);
        $downloadName = "{$docSlug}_{$siswaName}.pdf";

        return Storage::disk('public')->download($dokumen->file_path, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Halaman Cetak Sertifikat Kualitas Tinggi (HD A4 Landscape)
     */
    public function cetakSertifikat(DokumenSertifikatSiswa $dokumen)
    {
        $user = Auth::user();
        if (!self::canManage($user)) {
            if ((int) $dokumen->siswa_id !== (int) $user->id) {
                abort(403, 'Anda hanya dapat mencetak sertifikat milik Anda sendiri.');
            }
            if ((bool) ($user->akses_dokumen_dibatasi ?? false)) {
                abort(403, 'Akses dokumen dan sertifikat Anda sedang dibatasi karena administrasi belum selesai.');
            }
        }

        $siswa = User::with('kelas')->findOrFail($dokumen->siswa_id);
        return view('sertifikat.print', compact('dokumen', 'siswa'));
    }

    /**
     * Helper internal: Buat & simpan PDF sertifikat otomatis untuk 1 siswa
     */
    protected function createGeneratedCertificateForStudent(
        User $siswa,
        string $kategori,
        JenisSertifikat $jenisObj,
        string $predikat,
        array $meta,
        string $nomorDokumen = ''
    ): DokumenSertifikatSiswa {
        // Hapus file sertifikat lama jika siswa sudah punya jenis sertifikat yang sama
        $existing = DokumenSertifikatSiswa::where('siswa_id', $siswa->id)
            ->where('kategori', $kategori)
            ->where('jenis_sertifikat_id', $jenisObj->id)
            ->get();

        foreach ($existing as $oldDoc) {
            if ($oldDoc->file_path && Storage::disk('public')->exists($oldDoc->file_path)) {
                Storage::disk('public')->delete($oldDoc->file_path);
            }
            $oldDoc->delete();
        }

        $namaSekolah = 'SMK PLUS AL HILAL';
        try {
            $setting = PengaturanSekolah::first();
            if ($setting && !empty($setting->nama_sekolah)) {
                $namaSekolah = $setting->nama_sekolah;
            }
        } catch (\Throwable $e) {}

        $namaKegiatan = $meta['nama_kegiatan'] ?: $jenisObj->nama_sertifikat;
        $tempatTerbit = $meta['tempat_terbit'] ?: 'Cirebon';
        $tanggalTerbit = $meta['tanggal_terbit'] ?: now()->translatedFormat('d F Y');

        $pdfBytes = SertifikatPdfGenerator::generatePdfBytes([
            'kategori'           => $kategori,
            'nama_sekolah'       => $namaSekolah,
            'nomor_dokumen'      => $nomorDokumen,
            'nama_siswa'         => $siswa->name,
            'nisn'               => $siswa->nisn ?: $siswa->username,
            'kelas'              => $siswa->kelas->nama_kelas ?? ($siswa->kelas->nama ?? ''),
            'nama_kegiatan'      => $namaKegiatan,
            'deskripsi_kegiatan' => $meta['deskripsi_kegiatan'] ?? '',
            'predikat'           => $predikat,
            'tempat_tanggal'     => "{$tempatTerbit}, {$tanggalTerbit}",
            'pejabat_1_jabatan'  => $meta['pejabat_1_jabatan'] ?? 'Kepala SMK Plus Al Hilal',
            'pejabat_1_nama'     => $meta['pejabat_1_nama'] ?? '',
            'pejabat_1_nip'      => $meta['pejabat_1_nip'] ?? '',
            'pejabat_2_jabatan'  => $meta['pejabat_2_jabatan'] ?? '',
            'pejabat_2_nama'     => $meta['pejabat_2_nama'] ?? '',
            'pejabat_2_nip'      => $meta['pejabat_2_nip'] ?? '',
        ]);

        $safeSlug = Str::slug($jenisObj->nama_sertifikat) ?: $kategori;
        $safeSiswa = Str::slug($siswa->name) ?: 'siswa';
        $filename = "{$safeSlug}_{$safeSiswa}_{$siswa->id}_" . time() . '.pdf';
        $storedPath = "dokumen_sertifikat/{$kategori}/{$filename}";

        Storage::disk('public')->put($storedPath, $pdfBytes);

        return DokumenSertifikatSiswa::create([
            'siswa_id'            => $siswa->id,
            'kategori'            => $kategori,
            'jenis_sertifikat_id' => $jenisObj->id,
            'nama_dokumen'        => $jenisObj->nama_sertifikat,
            'nomor_dokumen'       => $nomorDokumen ?: null,
            'file_path'           => $storedPath,
            'file_name'           => $filename,
            'file_size'           => strlen($pdfBytes),
            'keterangan'          => "Predikat: {$predikat}",
            'uploaded_by'         => Auth::id(),
            'is_generated'        => true,
            'nama_kegiatan'       => $namaKegiatan,
            'deskripsi_kegiatan'  => $meta['deskripsi_kegiatan'] ?? null,
            'predikat'            => $predikat,
            'tempat_terbit'       => $tempatTerbit,
            'tanggal_terbit'      => $tanggalTerbit,
            'pejabat_1_jabatan'   => $meta['pejabat_1_jabatan'] ?? 'Kepala SMK Plus Al Hilal',
            'pejabat_1_nama'      => $meta['pejabat_1_nama'] ?? null,
            'pejabat_1_nip'       => $meta['pejabat_1_nip'] ?? null,
            'pejabat_2_jabatan'   => $meta['pejabat_2_jabatan'] ?? null,
            'pejabat_2_nama'      => $meta['pejabat_2_nama'] ?? null,
            'pejabat_2_nip'       => $meta['pejabat_2_nip'] ?? null,
        ]);
    }

    /**
     * Generate Sertifikat Otomatis Secara Massal (dari Form Checklist Siswa & Predikat Nilai)
     * Khusus Kepesantrenan & Kewirausahaan
     */
    public function generateMassal(Request $request, string $kategori)
    {
        $this->authorizeAdmin($kategori);
        $this->ensureSchema();

        if (!in_array($kategori, ['kepesantrenan', 'kewirausahaan'], true)) {
            abort(400, 'Fitur generate sertifikat otomatis hanya untuk Kepesantrenan dan Kewirausahaan.');
        }

        $request->validate([
            'jenis_sertifikat_id'  => 'nullable|exists:jenis_sertifikats,id',
            'nama_sertifikat_baru' => 'nullable|string|max:200',
            'deskripsi_kegiatan'   => 'nullable|string|max:500',
            'format_nomor'         => 'nullable|string|max:120',
            'tempat_terbit'        => 'nullable|string|max:100',
            'tanggal_terbit'       => 'nullable|string|max:100',
            'pejabat_1_jabatan'    => 'required|string|max:150',
            'pejabat_1_nama'       => 'required|string|max:150',
            'pejabat_1_nip'        => 'nullable|string|max:100',
            'pejabat_2_jabatan'    => 'nullable|string|max:150',
            'pejabat_2_nama'       => 'nullable|string|max:150',
            'pejabat_2_nip'        => 'nullable|string|max:100',
            'predikat_siswa'       => 'required|array',
        ]);

        if ($request->filled('nama_sertifikat_baru')) {
            $namaKegiatan = trim($request->nama_sertifikat_baru);
            $jenisObj = JenisSertifikat::firstOrCreate(
                ['kategori' => $kategori, 'nama_sertifikat' => $namaKegiatan],
                ['is_aktif' => true]
            );
        } elseif ($request->filled('jenis_sertifikat_id')) {
            $jenisObj = JenisSertifikat::findOrFail($request->jenis_sertifikat_id);
        } else {
            return redirect()->back()->with('error', 'Silakan pilih Nama Kegiatan / Sertifikat atau ketik nama kegiatan baru.');
        }

        $predikatMap = (array) $request->input('predikat_siswa', []);
        $validPredikats = ['Sangat Baik', 'Baik', 'Cukup'];

        $meta = [
            'nama_kegiatan'      => $jenisObj->nama_sertifikat,
            'deskripsi_kegiatan' => $request->input('deskripsi_kegiatan'),
            'tempat_terbit'      => $request->input('tempat_terbit', 'Cirebon'),
            'tanggal_terbit'     => $request->input('tanggal_terbit', now()->translatedFormat('d F Y')),
            'pejabat_1_jabatan'  => $request->input('pejabat_1_jabatan'),
            'pejabat_1_nama'     => $request->input('pejabat_1_nama'),
            'pejabat_1_nip'      => $request->input('pejabat_1_nip'),
            'pejabat_2_jabatan'  => $request->input('pejabat_2_jabatan'),
            'pejabat_2_nama'     => $request->input('pejabat_2_nama'),
            'pejabat_2_nip'      => $request->input('pejabat_2_nip'),
        ];

        $formatNomor = trim((string) $request->input('format_nomor', ''));
        $generatedCount = 0;
        $seq = 1;

        foreach ($predikatMap as $siswaId => $predikat) {
            $predikat = trim((string) $predikat);
            if (!in_array($predikat, $validPredikats, true)) {
                continue;
            }

            $siswa = User::with('kelas')->where('role', 'siswa')->find($siswaId);
            if (!$siswa) {
                continue;
            }

            $nomor = $formatNomor;
            if ($nomor !== '') {
                $noStr = str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
                if (str_contains($nomor, '{NO}')) {
                    $nomor = str_replace('{NO}', $noStr, $nomor);
                }
            }

            $this->createGeneratedCertificateForStudent($siswa, $kategori, $jenisObj, $predikat, $meta, $nomor);
            $generatedCount++;
            $seq++;
        }

        if ($generatedCount === 0) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih predikat nilainya (Sangat Baik / Baik / Cukup).');
        }

        return redirect()->back()->with('success', "Berhasil men-generate otomatis {$generatedCount} file PDF sertifikat \"{$jenisObj->nama_sertifikat}\" untuk masing-masing siswa!");
    }

    /**
     * Unduh Template Dokumen Data Siswa (CSV / Excel) untuk Generate Sertifikat dari 1 Dokumen
     */
    public function downloadTemplateGenerator(Request $request, string $kategori)
    {
        $this->authorizeAdmin($kategori);
        $this->ensureSchema();

        $kelasId = $request->query('kelas_id');
        $query = User::where('role', 'siswa')
            ->where('is_active', true)
            ->with('kelas')
            ->orderBy('kelas_id')
            ->orderBy('name');

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $siswas = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_data_sertifikat_' . $kategori . '.csv"',
        ];

        $callback = function () use ($siswas) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID_SISWA', 'NISN', 'NAMA_SISWA', 'KELAS', 'PREDIKAT_NILAI', 'NOMOR_SERTIFIKAT']);
            foreach ($siswas as $idx => $s) {
                $noStr = str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
                fputcsv($out, [
                    $s->id,
                    $s->nisn ?: $s->username,
                    $s->name,
                    $s->kelas->nama_kelas ?? ($s->kelas->nama ?? '-'),
                    'Sangat Baik', // Contoh isian: Sangat Baik / Baik / Cukup
                    "421.5/{$noStr}/SERT-AH/" . date('Y'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Upload 1 Dokumen Data (Excel .xlsx / .xls / .csv) -> Sistem Generate Sertifikat untuk Masing-Masing Siswa
     */
    public function importDataGenerator(Request $request, string $kategori)
    {
        $this->authorizeAdmin($kategori);
        $this->ensureSchema();

        if (!in_array($kategori, ['kepesantrenan', 'kewirausahaan'], true)) {
            abort(400);
        }

        $request->validate([
            'file_data'            => 'required|file|max:5120',
            'jenis_sertifikat_id'  => 'nullable|exists:jenis_sertifikats,id',
            'nama_sertifikat_baru' => 'nullable|string|max:200',
            'deskripsi_kegiatan'   => 'nullable|string|max:500',
            'tempat_terbit'        => 'nullable|string|max:100',
            'tanggal_terbit'       => 'nullable|string|max:100',
            'pejabat_1_jabatan'    => 'required|string|max:150',
            'pejabat_1_nama'       => 'required|string|max:150',
            'pejabat_1_nip'        => 'nullable|string|max:100',
            'pejabat_2_jabatan'    => 'nullable|string|max:150',
            'pejabat_2_nama'       => 'nullable|string|max:150',
            'pejabat_2_nip'        => 'nullable|string|max:100',
        ]);

        if ($request->filled('nama_sertifikat_baru')) {
            $namaKegiatan = trim($request->nama_sertifikat_baru);
            $jenisObj = JenisSertifikat::firstOrCreate(
                ['kategori' => $kategori, 'nama_sertifikat' => $namaKegiatan],
                ['is_aktif' => true]
            );
        } elseif ($request->filled('jenis_sertifikat_id')) {
            $jenisObj = JenisSertifikat::findOrFail($request->jenis_sertifikat_id);
        } else {
            return redirect()->back()->with('error', 'Silakan pilih Nama Kegiatan / Sertifikat atau ketik nama kegiatan baru.');
        }

        $file = $request->file('file_data');
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($ext, ['xlsx', 'xls'], true) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } else {
            $content = file_get_contents($file->getRealPath());
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            $lines = preg_split('/\r\n|\r|\n/', trim($content));
            foreach ($lines as $line) {
                if (trim($line) === '') {
                    continue;
                }
                $delimiter = str_contains($line, ';') && !str_contains($line, ',') ? ';' : ',';
                $rows[] = str_getcsv($line, $delimiter);
            }
        }

        $meta = [
            'nama_kegiatan'      => $jenisObj->nama_sertifikat,
            'deskripsi_kegiatan' => $request->input('deskripsi_kegiatan'),
            'tempat_terbit'      => $request->input('tempat_terbit', 'Cirebon'),
            'tanggal_terbit'     => $request->input('tanggal_terbit', now()->translatedFormat('d F Y')),
            'pejabat_1_jabatan'  => $request->input('pejabat_1_jabatan'),
            'pejabat_1_nama'     => $request->input('pejabat_1_nama'),
            'pejabat_1_nip'      => $request->input('pejabat_1_nip'),
            'pejabat_2_jabatan'  => $request->input('pejabat_2_jabatan'),
            'pejabat_2_nama'     => $request->input('pejabat_2_nama'),
            'pejabat_2_nip'      => $request->input('pejabat_2_nip'),
        ];

        $generatedCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0 && isset($row[0]) && !is_numeric(trim((string) $row[0]))) {
                // Skip header row
                continue;
            }

            $idSiswa   = trim((string) ($row[0] ?? ''));
            $nisn      = trim((string) ($row[1] ?? ''));
            $namaSiswa = trim((string) ($row[2] ?? ''));
            $predikat  = trim((string) ($row[4] ?? 'Baik'));
            $nomorDoc  = trim((string) ($row[5] ?? ''));

            if ($predikat === '' || strtolower($predikat) === '-') {
                continue;
            }

            // Normalisasi penulisan predikat
            $lowerPred = strtolower($predikat);
            if (str_contains($lowerPred, 'sangat')) {
                $predikat = 'Sangat Baik';
            } elseif (str_contains($lowerPred, 'cukup')) {
                $predikat = 'Cukup';
            } else {
                $predikat = 'Baik';
            }

            $siswa = null;
            if (is_numeric($idSiswa)) {
                $siswa = User::with('kelas')->where('role', 'siswa')->find((int) $idSiswa);
            }
            if (!$siswa && $nisn !== '') {
                $siswa = User::with('kelas')->where('role', 'siswa')
                    ->where(fn($q) => $q->where('nisn', $nisn)->orWhere('username', $nisn))
                    ->first();
            }
            if (!$siswa && $namaSiswa !== '') {
                $siswa = User::with('kelas')->where('role', 'siswa')
                    ->where('name', 'like', $namaSiswa)
                    ->first();
            }

            if (!$siswa) {
                continue;
            }

            $this->createGeneratedCertificateForStudent($siswa, $kategori, $jenisObj, $predikat, $meta, $nomorDoc);
            $generatedCount++;
        }

        if ($generatedCount === 0) {
            return redirect()->back()->with('error', 'Tidak ada data siswa yang cocok dari dokumen yang diunggah. Pastikan menggunakan format Template Data Sertifikat.');
        }

        return redirect()->back()->with('success', "Berhasil membaca 1 dokumen data dan men-generate otomatis {$generatedCount} sertifikat PDF \"{$jenisObj->nama_sertifikat}\" untuk masing-masing siswa!");
    }

    /**
     * Pratinjau (Preview) Hasil Sertifikat PDF secara langsung sebelum di-generate ke database
     */
    public function previewDraft(Request $request, string $kategori)
    {
        $this->authorizeAdmin($kategori);

        if (!in_array($kategori, ['kepesantrenan', 'kewirausahaan'], true)) {
            abort(400);
        }

        $namaSekolah = 'SMK PLUS AL HILAL';
        try {
            $setting = PengaturanSekolah::first();
            if ($setting && !empty($setting->nama_sekolah)) {
                $namaSekolah = $setting->nama_sekolah;
            }
        } catch (\Throwable $e) {}

        $namaKegiatan = trim((string) $request->query('nama_kegiatan', ''));
        if ($namaKegiatan === '') {
            $namaKegiatan = $kategori === 'kepesantrenan'
                ? 'Sertifikat Kompetensi Kepesantrenan'
                : 'Sertifikat Kompetensi Kewirausahaan';
        }

        $formatNomor = trim((string) $request->query('format_nomor', '421.5/{NO}/SERT-AH/' . date('Y')));
        $nomorContoh = str_contains($formatNomor, '{NO}')
            ? str_replace('{NO}', '001', $formatNomor)
            : ($formatNomor ?: '421.5/001/SERT-AH/' . date('Y'));

        $tempatTerbit  = trim((string) $request->query('tempat_terbit', 'Cirebon')) ?: 'Cirebon';
        $tanggalTerbit = trim((string) $request->query('tanggal_terbit', now()->translatedFormat('d F Y'))) ?: now()->translatedFormat('d F Y');
        $predikat      = trim((string) $request->query('predikat', 'Sangat Baik')) ?: 'Sangat Baik';
        $namaSiswa     = trim((string) $request->query('nama_siswa', 'CONTOH NAMA SISWA')) ?: 'CONTOH NAMA SISWA';
        $nisn          = trim((string) $request->query('nisn', '0081234567')) ?: '0081234567';
        $kelas         = trim((string) $request->query('kelas', 'XII TKJ 1')) ?: 'XII TKJ 1';

        $pdfBytes = SertifikatPdfGenerator::generatePdfBytes([
            'kategori'           => $kategori,
            'nama_sekolah'       => $namaSekolah,
            'nomor_dokumen'      => $nomorContoh,
            'nama_siswa'         => $namaSiswa,
            'nisn'               => $nisn,
            'kelas'              => $kelas,
            'nama_kegiatan'      => $namaKegiatan,
            'deskripsi_kegiatan' => trim((string) $request->query('deskripsi_kegiatan', '')),
            'predikat'           => $predikat,
            'tempat_tanggal'     => "{$tempatTerbit}, {$tanggalTerbit}",
            'pejabat_1_jabatan'  => trim((string) $request->query('pejabat_1_jabatan', 'Kepala SMK Plus Al Hilal')) ?: 'Kepala SMK Plus Al Hilal',
            'pejabat_1_nama'     => trim((string) $request->query('pejabat_1_nama', '')),
            'pejabat_1_nip'      => trim((string) $request->query('pejabat_1_nip', '')),
            'pejabat_2_jabatan'  => trim((string) $request->query('pejabat_2_jabatan', '')),
            'pejabat_2_nama'     => trim((string) $request->query('pejabat_2_nama', '')),
            'pejabat_2_nip'      => trim((string) $request->query('pejabat_2_nip', '')),
        ]);

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Preview_Sertifikat_' . Str::slug($namaKegiatan) . '.pdf"',
        ]);
    }
}

