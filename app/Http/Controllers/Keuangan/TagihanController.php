<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\TagihanMaster;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tab     = $request->query('tab', 'siswa'); // 'siswa' or 'master'
        $kelasId = $request->query('kelas_id');
        $search  = $request->query('search');

        $masters = TagihanMaster::orderBy('created_at', 'desc')->get();

        $query = User::where('role', 'siswa')
            ->where('is_active', true)
            ->with(['tagihans.master', 'tagihans.pembayarans', 'kelasModel']);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswas = $query->orderBy('name')->get();

        $kelasList = \App\Models\Kelas::orderBy('nama')->get();

        return view('keuangan.tagihan.index', compact('tab', 'masters', 'siswas', 'kelasList', 'kelasId', 'search'));
    }

    /**
     * Store a newly created resource in storage (Single Tagihan ke Siswa).
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:users,id',
            'tagihan_master_id' => 'required|exists:tagihan_masters,id',
            'nominal' => 'nullable|numeric|min:0',
            'bulan' => 'nullable|string',
        ]);

        $master = TagihanMaster::findOrFail($request->tagihan_master_id);
        $tahunAjaranId = $master->tahun_ajaran_id ?? \App\Models\PengaturanSekolah::getActiveTahunAjaranId();

        // Nominal otomatis dari master tagihan jika admin tidak mengetik/mengubahnya
        $nominal = ($request->filled('nominal') && $request->nominal > 0) ? (float)$request->nominal : (float)$master->nominal;

        $bulan = $request->bulan;
        if ($master->jenis === 'spp') {
            if ($bulan) {
                try {
                    $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y');
                } catch (\Exception $e) {
                    $namaBulan = $bulan;
                }

                if (stripos($master->nama_tagihan, $namaBulan) !== false) {
                    $namaTagihan = $master->nama_tagihan;
                } else {
                    $namaTagihan = $master->nama_tagihan . ' - ' . $namaBulan;
                }
            } else {
                $namaTagihan = $master->nama_tagihan;
            }
        } else {
            $namaTagihan = $master->nama_tagihan;
        }

        // CEK DUPLIKASI KETAT: Mencegah tagihan ganda
        $cek = Tagihan::where('siswa_id', $request->siswa_id)
            ->where('tahun_ajaran_id', $tahunAjaranId);

        if ($master->jenis === 'spp') {
            $cek->where(function($q) use ($master, $bulan, $namaTagihan) {
                $q->where(function($sq) use ($master, $bulan) {
                    $sq->where('tagihan_master_id', $master->id);
                    if ($bulan) {
                        $sq->where('bulan', $bulan);
                    }
                })->orWhere('nama_tagihan', $namaTagihan);
            });
        } else {
            $cek->where(function($q) use ($master, $namaTagihan) {
                $q->where('tagihan_master_id', $master->id)
                  ->orWhere('nama_tagihan', $namaTagihan);
            });
        }

        if ($cek->exists()) {
            return back()->with('error', "Gagal: Tagihan '{$namaTagihan}' sudah ada untuk siswa ini! Sistem menolak input tagihan ganda.");
        }

        Tagihan::create([
            'siswa_id' => $request->siswa_id,
            'tagihan_master_id' => $master->id,
            'nama_tagihan' => $namaTagihan,
            'jenis' => $master->jenis,
            'bulan' => $bulan,
            'nominal' => $nominal,
            'terbayar' => 0,
            'status' => 'belum_lunas',
            'tahun_ajaran_id' => $tahunAjaranId,
        ]);

        return back()->with('success', "Tagihan '{$namaTagihan}' berhasil ditambahkan ke siswa.");
    }


    /**
     * Store a newly created resource in storage (Master Tagihan).
     */
    public function storeMaster(Request $request)
    {
        $request->validate([
            'nama_tagihan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'is_rutin' => 'nullable|boolean',
            'tingkat_kelas' => 'nullable|string',
            'jurusan' => 'nullable|string',
        ]);

        TagihanMaster::create([
            'nama_tagihan' => $request->nama_tagihan,
            'jenis' => $request->jenis,
            'nominal' => $request->nominal,
            'is_rutin' => $request->boolean('is_rutin'),
            'tingkat_kelas' => $request->tingkat_kelas,
            'jurusan' => $request->jurusan,
            'keterangan' => $request->keterangan,
            'tahun_ajaran_id' => \App\Models\PengaturanSekolah::getActiveTahunAjaranId()
        ]);

        return redirect()->route('keuangan.tagihan.index', ['tab' => 'master'])->with('success', 'Master Tagihan berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateMaster(Request $request, $id)
    {
        $master = TagihanMaster::findOrFail($id);
        
        $request->validate([
            'nama_tagihan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'is_rutin' => 'nullable|boolean',
            'tingkat_kelas' => 'nullable|string',
            'jurusan' => 'nullable|string',
        ]);

        $master->update([
            'nama_tagihan' => $request->nama_tagihan,
            'jenis' => $request->jenis,
            'nominal' => $request->nominal,
            'is_rutin' => $request->boolean('is_rutin'),
            'tingkat_kelas' => $request->tingkat_kelas,
            'jurusan' => $request->jurusan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('keuangan.tagihan.index', ['tab' => 'master'])->with('success', 'Master Tagihan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyMaster($id)
    {
        $master = TagihanMaster::findOrFail($id);
        $master->delete();
        return redirect()->route('keuangan.tagihan.index', ['tab' => 'master'])->with('success', 'Master Tagihan berhasil dihapus.');
    }

    /**
     * Generate Tagihan ke Siswa
     */
    public function generateTagihan(Request $request)
    {
        $request->validate([
            'tagihan_master_id' => 'required|exists:tagihan_masters,id',
            'target' => 'required|string', // 'semua', 'kelas_tertentu', 'jurusan_tertentu', 'siswa_tertentu'
            'kelas_id' => 'nullable|exists:kelas,id',
            'jurusan' => 'nullable|string',
            'siswa_id' => 'nullable|array',
            'siswa_id.*' => 'exists:users,id',
            'bulan' => 'nullable|string'
        ]);

        $master = TagihanMaster::findOrFail($request->tagihan_master_id);
        $tahunAjaranId = $master->tahun_ajaran_id ?? \App\Models\PengaturanSekolah::getActiveTahunAjaranId();
        
        $query = User::where('role', 'siswa')->where('is_active', true);

        // Filter otomatis sesuai spesifikasi Master Tagihan
        if ($master->tingkat_kelas) {
            $query->whereHas('kelasModel', function($q) use ($master) {
                $q->where('tingkat', $master->tingkat_kelas);
            });
        }

        if ($master->jurusan && strtolower($master->jurusan) !== 'semua') {
            $jur = $master->jurusan;
            $query->where(function($q) use ($jur) {
                $q->where('jurusan', $jur)
                  ->orWhereHas('kelasModel', function($qk) use ($jur) {
                      $qk->where('jurusan', $jur);
                  });
            });
        }

        if ($request->target === 'kelas_tertentu' && $request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        } elseif ($request->target === 'jurusan_tertentu' && $request->jurusan) {
            $targetJurusan = $request->jurusan;
            $query->where(function($q) use ($targetJurusan) {
                $q->where('jurusan', $targetJurusan)
                  ->orWhereHas('kelasModel', function($qk) use ($targetJurusan) {
                      $qk->where('jurusan', $targetJurusan);
                  });
            });
        } elseif ($request->target === 'siswa_tertentu' && is_array($request->siswa_id)) {
            $query->whereIn('id', $request->siswa_id);
        }

        $siswas = $query->get();
        $count = 0;
        $skipped = 0;

        $bulan = $request->bulan;
        if ($master->jenis === 'spp') {
            if ($bulan) {
                try {
                    $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y');
                } catch (\Exception $e) {
                    $namaBulan = $bulan;
                }

                if (stripos($master->nama_tagihan, $namaBulan) !== false) {
                    $namaTagihan = $master->nama_tagihan;
                } else {
                    $namaTagihan = $master->nama_tagihan . ' - ' . $namaBulan;
                }
            } else {
                $namaTagihan = $master->nama_tagihan;
            }
        } else {
            $namaTagihan = $master->nama_tagihan;
        }

        foreach ($siswas as $siswa) {
            $existingQuery = Tagihan::where('siswa_id', $siswa->id)
                ->where('tahun_ajaran_id', $tahunAjaranId);

            if ($master->jenis === 'spp') {
                $existingQuery->where(function($q) use ($master, $bulan, $namaTagihan) {
                    $q->where(function($sq) use ($master, $bulan) {
                        $sq->where('tagihan_master_id', $master->id);
                        if ($bulan) {
                            $sq->where('bulan', $bulan);
                        }
                    })->orWhere('nama_tagihan', $namaTagihan);
                });
            } else {
                $existingQuery->where(function($q) use ($master, $namaTagihan) {
                    $q->where('tagihan_master_id', $master->id)
                      ->orWhere('nama_tagihan', $namaTagihan);
                });
            }
            
            if (!$existingQuery->exists()) {
                Tagihan::create([
                    'siswa_id' => $siswa->id,
                    'tagihan_master_id' => $master->id,
                    'nama_tagihan' => $namaTagihan,
                    'jenis' => $master->jenis,
                    'bulan' => $bulan,
                    'nominal' => $master->nominal,
                    'terbayar' => 0,
                    'status' => 'belum_lunas',
                    'tahun_ajaran_id' => $tahunAjaranId,
                ]);
                $count++;
            } else {
                $skipped++;
            }
        }

        $message = "Berhasil membuat tagihan '{$namaTagihan}' untuk {$count} siswa.";
        if ($skipped > 0) {
            $message .= " ({$skipped} siswa dilewati karena sudah memiliki tagihan ini sebelumnya).";
        }

        return back()->with('success', $message);
    }

    /**
     * Generate semua master tagihan rutin ke seluruh siswa yang berhak (1 Klik Cerdas)
     */
    public function generateRutin(Request $request)
    {
        $tahunAjaranId = \App\Models\PengaturanSekolah::getActiveTahunAjaranId();
        $rutinMasters = TagihanMaster::where('is_rutin', true)
            ->where(function($q) use ($tahunAjaranId) {
                $q->whereNull('tahun_ajaran_id')
                  ->orWhere('tahun_ajaran_id', $tahunAjaranId);
            })
            ->get();

        if ($rutinMasters->isEmpty()) {
            return back()->with('error', 'Tidak ada master tagihan yang ditandai sebagai Rutin Otomatis.');
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($rutinMasters as $master) {
            $query = User::where('role', 'siswa')->where('is_active', true);

            if ($master->tingkat_kelas) {
                $query->whereHas('kelasModel', function($q) use ($master) {
                    $q->where('tingkat', $master->tingkat_kelas);
                });
            }

            if ($master->jurusan && strtolower($master->jurusan) !== 'semua') {
                $jur = $master->jurusan;
                $query->where(function($q) use ($jur) {
                    $q->where('jurusan', $jur)
                      ->orWhereHas('kelasModel', function($qk) use ($jur) {
                          $qk->where('jurusan', $jur);
                      });
                });
            }

            $siswas = $query->get();

            foreach ($siswas as $siswa) {
                $cek = Tagihan::where('siswa_id', $siswa->id)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->where(function($q) use ($master) {
                        $q->where('tagihan_master_id', $master->id)
                          ->orWhere('nama_tagihan', $master->nama_tagihan);
                    });

                if (!$cek->exists()) {
                    Tagihan::create([
                        'siswa_id' => $siswa->id,
                        'tagihan_master_id' => $master->id,
                        'nama_tagihan' => $master->nama_tagihan,
                        'jenis' => $master->jenis,
                        'bulan' => null,
                        'nominal' => $master->nominal,
                        'terbayar' => 0,
                        'status' => 'belum_lunas',
                        'tahun_ajaran_id' => $tahunAjaranId,
                    ]);
                    $totalCreated++;
                } else {
                    $totalSkipped++;
                }
            }
        }

        return back()->with('success', "Proses Selesai: Berhasil membuat {$totalCreated} tagihan rutin otomatis ke siswa. ({$totalSkipped} tagihan dilewati otomatis karena sudah pernah dibuat).");
    }

    /**
     * Bersihkan data tagihan yang duplikat (yang belum ada pembayaran terbayar = 0)
     */
    public function cleanDuplicates()
    {
        $allTagihans = Tagihan::where('terbayar', 0)
            ->orderBy('id', 'asc')
            ->get();

        $seen = [];
        $deleted = 0;

        foreach ($allTagihans as $tagihan) {
            $key = $tagihan->siswa_id . '_' . trim(strtolower($tagihan->nama_tagihan)) . '_' . ($tagihan->bulan ?? '') . '_' . ($tagihan->tahun_ajaran_id ?? '');
            
            if (isset($seen[$key])) {
                $tagihan->delete();
                $deleted++;
            } else {
                $seen[$key] = $tagihan->id;
            }
        }

        return back()->with('success', "Pembersihan Selesai: Berhasil menemukan dan menghapus {$deleted} tagihan duplikat.");
    }

    /**
     * Hapus satu item tagihan siswa jika belum dibayar
     */
    public function destroy($id)
    {
        $tagihan = Tagihan::findOrFail($id);

        if ($tagihan->terbayar > 0) {
            return back()->with('error', "Tagihan '{$tagihan->nama_tagihan}' tidak dapat dihapus karena sudah memiliki riwayat pembayaran (Terbayar: Rp " . number_format($tagihan->terbayar, 0, ',', '.') . ").");
        }

        $nama = $tagihan->nama_tagihan;
        $tagihan->delete();

        return back()->with('success', "Tagihan '{$nama}' berhasil dibatalkan/dihapus.");
    }

    /**
     * Display the specified resource.
     * Use to show specific student tagihan.
     */
    public function show($id)
    {
        $siswa = User::where('role', 'siswa')->findOrFail($id);
        $tagihans = Tagihan::with('master')->where('siswa_id', $id)->get();
        $pembayarans = Pembayaran::whereHas('tagihan', function($q) use ($id) {
            $q->where('siswa_id', $id);
        })->with('tagihan')->orderBy('created_at', 'desc')->get();

        return view('keuangan.tagihan.show', compact('siswa', 'tagihans', 'pembayarans'));
    }

    /**
     * View Tagihan for Siswa
     */
    public function siswaTagihan()
    {
        $siswa = auth()->user();
        $tagihans = Tagihan::with('master')->where('siswa_id', $siswa->id)->get();
        $pembayarans = Pembayaran::whereHas('tagihan', function($q) use ($siswa) {
            $q->where('siswa_id', $siswa->id);
        })->with('tagihan')->orderBy('created_at', 'desc')->get();

        return view('siswa.tagihan.index', compact('siswa', 'tagihans', 'pembayarans'));
    }

    /**
     * View Tagihan for Wali Kelas
     */
    public function walikelasTagihan()
    {
        $guru = auth()->user();
        // Get wali kelas class
        $kelas = \App\Models\Kelas::where('wali_kelas_id', $guru->id)->first();
        if (!$kelas) {
            return back()->with('error', 'Anda tidak terdaftar sebagai wali kelas aktif.');
        }

        $siswas = User::where('role', 'siswa')
            ->where('kelas_id', $kelas->id)
            ->where('is_active', true)
            ->with('tagihans.master')
            ->orderBy('name')
            ->get();

        return view('walikelas.tagihan.index', compact('kelas', 'siswas'));
    }
}

