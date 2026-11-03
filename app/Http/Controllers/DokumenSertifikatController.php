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
        } catch (\Throwable $e) {
            // Abaikan jika sedang koneksi read-only
        }
    }

    /**
     * Cek apakah user memiliki hak kelola dokumen & sertifikat (Superadmin & yang ditunjuk sebagai Admin)
     */
    public static function canManage(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $user->isSuperAdmin()
            || $user->role === 'admin'
            || !empty($user->admin_role)
            || $user->canManageAcademic();
    }

    protected function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!self::canManage($user)) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola Dokumen & Sertifikat.');
        }
    }

    /**
     * Halaman Pengelolaan Dokumen & Sertifikat (Superadmin & Admin)
     */
    public function adminIndex(Request $request, string $kategori = 'kepesantrenan')
    {
        $this->authorizeAdmin();
        $this->ensureSchema();

        if (!in_array($kategori, self::VALID_KATEGORIS, true)) {
            $kategori = 'kepesantrenan';
        }

        $config = DokumenSertifikatSiswa::getKategoriConfig($kategori);
        $kelasId = $request->query('kelas_id');
        $search = trim((string) $request->query('search', ''));
        $statusFilter = $request->query('status_upload');
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

        // Hitung statistik sebelum filter status_upload
        $totalSiswa = $allFilteredSiswas->count();
        $siswaSudahUpload = $allFilteredSiswas->filter(fn($s) => $dokumensBySiswa->has($s->id))->count();
        $siswaBelumUpload = $totalSiswa - $siswaSudahUpload;
        $totalFilePdf = $dokumensBySiswa->flatten(1)->count();

        // Terapkan filter status_upload jika dipilih
        if ($statusFilter === 'sudah') {
            $allFilteredSiswas = $allFilteredSiswas->filter(fn($s) => $dokumensBySiswa->has($s->id))->values();
        } elseif ($statusFilter === 'belum') {
            $allFilteredSiswas = $allFilteredSiswas->filter(fn($s) => !$dokumensBySiswa->has($s->id))->values();
        }

        return view('admin.dokumen_sertifikat.index', compact(
            'kategori',
            'config',
            'kelases',
            'kelasId',
            'search',
            'statusFilter',
            'jenisFilter',
            'jenisSertifikats',
            'allFilteredSiswas',
            'dokumensBySiswa',
            'totalSiswa',
            'siswaSudahUpload',
            'siswaBelumUpload',
            'totalFilePdf'
        ));
    }

    /**
     * Tambah Master Nama Sertifikat Baru (Kepesantrenan / Kewirausahaan)
     */
    public function storeJenisSertifikat(Request $request, string $kategori)
    {
        $this->authorizeAdmin();
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
        $this->authorizeAdmin();
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
        $this->authorizeAdmin();
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
        $this->authorizeAdmin();
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
        $this->authorizeAdmin();
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
            'countsByKategori'
        ));
    }

    /**
     * Stream / Preview File PDF di Browser (Aman dengan Otorisasi)
     */
    public function preview(DokumenSertifikatSiswa $dokumen)
    {
        $user = Auth::user();
        if (!self::canManage($user) && (int) $dokumen->siswa_id !== (int) $user->id) {
            abort(403, 'Anda hanya dapat melihat dokumen milik Anda sendiri.');
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
        if (!self::canManage($user) && (int) $dokumen->siswa_id !== (int) $user->id) {
            abort(403, 'Anda hanya dapat mengunduh dokumen milik Anda sendiri.');
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

