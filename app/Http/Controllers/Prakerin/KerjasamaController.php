<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\Kerjasama;
use App\Models\Dudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller Manajemen Kerjasama Mitra DU/DI (Dunia Usaha & Dunia Industri)
 * Modul Prakerin & Kemitraan SMK Plus Al Hilal
 */
class KerjasamaController extends Controller
{
    /**
     * Daftar preset bentuk kerjasama yang umum dibangun SMK dengan DU/DI
     */
    public static array $presetBentukKerjasama = [
        'Sinkronisasi Kurikulum',
        'Pelaksanaan Prakerin / PKL',
        'Guru Tamu / Praktisi Mengajar',
        'Rekrutmen & Penyaluran Lulusan',
        'Penggajian / Payroll',
        'Kelas Industri / Teaching Factory',
        'Uji Sertifikasi Kompetensi (LSP)',
        'Beasiswa Pendidikan & Pelatihan',
        'Kunjungan Industri (KI)',
        'Magang & Upskilling Guru',
        'Bantuan Alat & Bahan Praktik',
    ];

    /**
     * Menampilkan daftar kerjasama mitra DU/DI
     */
    public function index(Request $request)
    {
        $query = Kerjasama::with(['dudi.penempatans', 'creator']);

        // Pencarian teks
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('nama_mitra', 'like', "%{$s}%")
                  ->orWhere('bidang_mitra', 'like', "%{$s}%")
                  ->orWhere('alamat', 'like', "%{$s}%")
                  ->orWhere('nomor_mou', 'like', "%{$s}%")
                  ->orWhere('pic_nama', 'like', "%{$s}%")
                  ->orWhere('bentuk_kerjasama', 'like', "%{$s}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter bentuk kerjasama tertentu
        if ($request->filled('bentuk')) {
            $b = $request->bentuk;
            $query->where('bentuk_kerjasama', 'like', "%{$b}%");
        }

        // Filter tahun
        if ($request->filled('tahun')) {
            $t = $request->tahun;
            $query->where(function ($q) use ($t) {
                $q->where('tahun_mulai', $t)
                  ->orWhere('tahun_berakhir', $t);
            });
        }

        $kerjasamas = $query->orderBy('tahun_mulai', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15)
                            ->withQueryString();

        // Statistik ringkasan
        $currentYear    = (int) date('Y');
        $totalKerjasama = Kerjasama::count();
        $totalAktif     = Kerjasama::where('status', 'aktif')
                            ->where('tahun_berakhir', '>=', $currentYear)
                            ->count();
        $totalBerakhir  = Kerjasama::where(function ($q) use ($currentYear) {
                            $q->where('status', 'berakhir')
                              ->orWhere('tahun_berakhir', '<', $currentYear);
                        })->count();
        $totalMitraDudi = Dudi::count();

        $presetBentuk = self::$presetBentukKerjasama;

        return view('prakerin.kerjasama.index', compact(
            'kerjasamas',
            'totalKerjasama',
            'totalAktif',
            'totalBerakhir',
            'totalMitraDudi',
            'presetBentuk'
        ));
    }

    /**
     * Form tambah kerjasama baru
     */
    public function create()
    {
        $dudiList     = Dudi::orderBy('nama')->get();
        $presetBentuk = self::$presetBentukKerjasama;

        return view('prakerin.kerjasama.create', compact('dudiList', 'presetBentuk'));
    }

    /**
     * Simpan data kerjasama mitra baru & sinkronkan otomatis ke Mitra DU/DI Prakerin
     */
    public function store(Request $request)
    {
        // Normalisasi bentuk_kerjasama bila terkirim dalam string dipisah koma atau array
        $bentukInput = $request->input('bentuk_kerjasama', []);
        if (is_string($bentukInput)) {
            $bentukInput = array_filter(array_map('trim', explode(',', $bentukInput)));
        }

        // Tambah custom bentuk kerjasama bila ada diinput manual
        if ($request->filled('bentuk_kerjasama_custom')) {
            $customItems = array_filter(array_map('trim', explode(',', $request->bentuk_kerjasama_custom)));
            $bentukInput = array_unique(array_merge($bentukInput, $customItems));
        }

        $request->merge(['bentuk_kerjasama' => array_values($bentukInput)]);

        $request->validate([
            'nama_mitra'        => 'required|string|max:150',
            'bidang_mitra'      => 'nullable|string|max:100',
            'alamat'            => 'nullable|string',
            'no_telp'           => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:100',
            'nomor_mou'         => 'nullable|string|max:100',
            'bentuk_kerjasama'  => 'required|array|min:1',
            'tahun_mulai'       => 'required|string|max:10',
            'tahun_berakhir'    => 'required|string|max:10',
            'file_kerjasama'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
            'link_drive'        => 'nullable|url|max:500',
            'pic_nama'          => 'nullable|string|max:100',
            'pic_jabatan'       => 'nullable|string|max:100',
            'pic_kontak'        => 'nullable|string|max:50',
            'keterangan'        => 'nullable|string',
        ], [
            'nama_mitra.required'       => 'Nama Mitra DU/DI wajib diisi.',
            'bentuk_kerjasama.required' => 'Pilih atau tentukan minimal 1 bentuk kerjasama.',
            'bentuk_kerjasama.min'      => 'Pilih minimal 1 bentuk kerjasama.',
            'tahun_mulai.required'      => 'Tahun mulai kerjasama wajib diisi.',
            'tahun_berakhir.required'   => 'Tahun berakhir kerjasama wajib diisi.',
            'file_kerjasama.mimes'      => 'File kerjasama harus berformat PDF, DOC, DOCX, JPG, atau PNG.',
            'file_kerjasama.max'        => 'Ukuran file MoU maksimal 15 MB.',
            'link_drive.url'            => 'Format link Google Drive harus berupa tautan URL valid (contoh: https://drive.google.com/...).',
        ]);

        // 1. Tentukan status otomatis berdasarkan tahun berakhir
        $currentYear = (int) date('Y');
        $endYear     = (int) $request->tahun_berakhir;
        $status      = ($endYear > 0 && $endYear < $currentYear) ? 'berakhir' : ($request->input('status', 'aktif'));

        // 2. Upload file dokumen jika ada
        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file_kerjasama')) {
            $file     = $request->file('file_kerjasama');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('kerjasama_dokumen', 'public');
        }

        // 3. SINKRONISASI OTOMATIS KE MITRA DU/DI PRAKERIN
        $dudi = null;
        if ($request->filled('dudi_id')) {
            $dudi = Dudi::find($request->dudi_id);
        }

        if (!$dudi) {
            $dudi = Dudi::where('nama', $request->nama_mitra)->first();
        }

        if ($dudi) {
            // Perbarui data jika sebelumnya belum terisi
            $dudiUpdate = [];
            if (!$dudi->bidang_usaha && $request->filled('bidang_mitra')) {
                $dudiUpdate['bidang_usaha'] = $request->bidang_mitra;
            }
            if (!$dudi->alamat && $request->filled('alamat')) {
                $dudiUpdate['alamat'] = $request->alamat;
            }
            if (!$dudi->no_telp && $request->filled('no_telp')) {
                $dudiUpdate['no_telp'] = $request->no_telp;
            }
            if (!$dudi->email && $request->filled('email')) {
                $dudiUpdate['email'] = $request->email;
            }
            if (!empty($dudiUpdate)) {
                $dudi->update($dudiUpdate);
            }
        } else {
            // Mitra belum ada di sistem, otomatis buat di tabel dudi (Mitra Prakerin)
            $dudi = Dudi::create([
                'nama'         => $request->nama_mitra,
                'bidang_usaha' => $request->bidang_mitra,
                'alamat'       => $request->alamat,
                'no_telp'      => $request->no_telp,
                'email'        => $request->email,
                'status'       => true,
            ]);
        }

        // 4. Simpan record Kerjasama
        Kerjasama::create([
            'dudi_id'          => $dudi->id,
            'nama_mitra'       => $request->nama_mitra,
            'bidang_mitra'     => $request->bidang_mitra,
            'alamat'           => $request->alamat,
            'no_telp'          => $request->no_telp,
            'email'            => $request->email,
            'nomor_mou'        => $request->nomor_mou,
            'bentuk_kerjasama' => array_values($bentukInput),
            'tahun_mulai'      => $request->tahun_mulai,
            'tahun_berakhir'   => $request->tahun_berakhir,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'file_kerjasama'   => $filePath,
            'file_nama_asli'   => $fileName,
            'link_drive'       => $request->link_drive,
            'pic_nama'         => $request->pic_nama,
            'pic_jabatan'      => $request->pic_jabatan,
            'pic_kontak'       => $request->pic_kontak,
            'status'           => $status,
            'keterangan'       => $request->keterangan,
            'created_by'       => auth()->id(),
        ]);

        return redirect()->route('prakerin.kerjasama.index')
            ->with('success', "Data kerjasama dengan {$request->nama_mitra} berhasil ditambahkan dan otomatis terdaftar sebagai Mitra DU/DI Prakerin!");
    }

    /**
     * Tampilkan detail kerjasama
     */
    public function show(Kerjasama $kerjasama)
    {
        $kerjasama->load(['dudi.penempatans.siswa.kelas.jurusan', 'dudi.pembimbingDudi', 'creator']);
        return view('prakerin.kerjasama.show', compact('kerjasama'));
    }

    /**
     * Form edit kerjasama
     */
    public function edit(Kerjasama $kerjasama)
    {
        $dudiList     = Dudi::orderBy('nama')->get();
        $presetBentuk = self::$presetBentukKerjasama;

        return view('prakerin.kerjasama.edit', compact('kerjasama', 'dudiList', 'presetBentuk'));
    }

    /**
     * Update data kerjasama & perbarui data Mitra DU/DI
     */
    public function update(Request $request, Kerjasama $kerjasama)
    {
        $bentukInput = $request->input('bentuk_kerjasama', []);
        if (is_string($bentukInput)) {
            $bentukInput = array_filter(array_map('trim', explode(',', $bentukInput)));
        }

        if ($request->filled('bentuk_kerjasama_custom')) {
            $customItems = array_filter(array_map('trim', explode(',', $request->bentuk_kerjasama_custom)));
            $bentukInput = array_unique(array_merge($bentukInput, $customItems));
        }

        $request->merge(['bentuk_kerjasama' => array_values($bentukInput)]);

        $request->validate([
            'nama_mitra'        => 'required|string|max:150',
            'bidang_mitra'      => 'nullable|string|max:100',
            'alamat'            => 'nullable|string',
            'no_telp'           => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:100',
            'nomor_mou'         => 'nullable|string|max:100',
            'bentuk_kerjasama'  => 'required|array|min:1',
            'tahun_mulai'       => 'required|string|max:10',
            'tahun_berakhir'    => 'required|string|max:10',
            'file_kerjasama'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
            'link_drive'        => 'nullable|url|max:500',
            'pic_nama'          => 'nullable|string|max:100',
            'pic_jabatan'       => 'nullable|string|max:100',
            'pic_kontak'        => 'nullable|string|max:50',
            'keterangan'        => 'nullable|string',
        ], [
            'nama_mitra.required'       => 'Nama Mitra DU/DI wajib diisi.',
            'bentuk_kerjasama.required' => 'Pilih atau tentukan minimal 1 bentuk kerjasama.',
            'tahun_mulai.required'      => 'Tahun mulai kerjasama wajib diisi.',
            'tahun_berakhir.required'   => 'Tahun berakhir kerjasama wajib diisi.',
            'file_kerjasama.mimes'      => 'File kerjasama harus berformat PDF, DOC, DOCX, JPG, atau PNG.',
            'file_kerjasama.max'        => 'Ukuran file MoU maksimal 15 MB.',
            'link_drive.url'            => 'Format link Google Drive harus berupa tautan URL valid.',
        ]);

        $filePath = $kerjasama->file_kerjasama;
        $fileName = $kerjasama->file_nama_asli;

        // Jika ada unggahan file baru
        if ($request->hasFile('file_kerjasama')) {
            // Hapus file lama jika ada
            if ($kerjasama->file_kerjasama && Storage::disk('public')->exists($kerjasama->file_kerjasama)) {
                Storage::disk('public')->delete($kerjasama->file_kerjasama);
            }
            $file     = $request->file('file_kerjasama');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('kerjasama_dokumen', 'public');
        }

        // Sinkronisasi status
        $currentYear = (int) date('Y');
        $endYear     = (int) $request->tahun_berakhir;
        $status      = $request->input('status', ($endYear > 0 && $endYear < $currentYear) ? 'berakhir' : 'aktif');

        // Sinkronkan ke Dudi
        $dudi = $kerjasama->dudi;
        if ($dudi) {
            $dudi->update([
                'nama'         => $request->nama_mitra,
                'bidang_usaha' => $request->bidang_mitra ?? $dudi->bidang_usaha,
                'alamat'       => $request->alamat ?? $dudi->alamat,
                'no_telp'      => $request->no_telp ?? $dudi->no_telp,
                'email'        => $request->email ?? $dudi->email,
            ]);
        }

        $kerjasama->update([
            'nama_mitra'       => $request->nama_mitra,
            'bidang_mitra'     => $request->bidang_mitra,
            'alamat'           => $request->alamat,
            'no_telp'          => $request->no_telp,
            'email'            => $request->email,
            'nomor_mou'        => $request->nomor_mou,
            'bentuk_kerjasama' => array_values($bentukInput),
            'tahun_mulai'      => $request->tahun_mulai,
            'tahun_berakhir'   => $request->tahun_berakhir,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'file_kerjasama'   => $filePath,
            'file_nama_asli'   => $fileName,
            'link_drive'       => $request->link_drive,
            'pic_nama'         => $request->pic_nama,
            'pic_jabatan'      => $request->pic_jabatan,
            'pic_kontak'       => $request->pic_kontak,
            'status'           => $status,
            'keterangan'       => $request->keterangan,
        ]);

        return redirect()->route('prakerin.kerjasama.index')
            ->with('success', "Data kerjasama {$kerjasama->nama_mitra} berhasil diperbarui.");
    }

    /**
     * Hapus data kerjasama
     */
    public function destroy(Kerjasama $kerjasama)
    {
        $namaMitra = $kerjasama->nama_mitra;

        if ($kerjasama->file_kerjasama && Storage::disk('public')->exists($kerjasama->file_kerjasama)) {
            Storage::disk('public')->delete($kerjasama->file_kerjasama);
        }

        $kerjasama->delete();

        return redirect()->route('prakerin.kerjasama.index')
            ->with('success', "Data kerjasama dengan {$namaMitra} berhasil dihapus.");
    }

    /**
     * Download atau lihat file dokumen MoU kerjasama
     */
    public function downloadFile(Kerjasama $kerjasama)
    {
        if ($kerjasama->hasLocalFile()) {
            $ext = pathinfo($kerjasama->file_kerjasama, PATHINFO_EXTENSION);
            $downloadName = ($kerjasama->file_nama_asli) 
                ?: 'MoU_' . Str::slug($kerjasama->nama_mitra) . '.' . $ext;

            return Storage::disk('public')->download($kerjasama->file_kerjasama, $downloadName);
        }

        if (!empty($kerjasama->link_drive)) {
            return redirect()->away($kerjasama->link_drive);
        }

        return redirect()->back()->with('error', 'File dokumen kerjasama tidak ditemukan di server.');
    }
}
