<?php

namespace App\Http\Controllers;

use App\Models\DokumenSertifikatSiswa;
use App\Models\JenisSertifikat;
use App\Models\Kelas;
use App\Models\User;
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
                    $table->timestamps();
                });
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

        $kelases = Kelas::orderBy('nama_kelas')->get();

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
            'totalFilePdf'
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
}

