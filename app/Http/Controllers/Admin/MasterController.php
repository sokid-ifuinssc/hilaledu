<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Exports\MasterDataExport;
use App\Exports\TemplateExport;
use App\Imports\MasterDataImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    // ============== TAHUN AJARAN ==============

    public function tahunAjaran()
    {
        $tahunAjarans = TahunAjaran::orderByDesc('tahun_mulai')->get();
        return view('admin.master.tahun-ajaran', compact('tahunAjarans'));
    }

    public function storeTahunAjaran(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:20|unique:tahun_ajarans,nama',
            'tahun_mulai'   => 'required|digits:4',
            'tahun_selesai' => 'required|digits:4',
        ], [
            'nama.unique' => 'Nama tahun ajaran sudah terdaftar.',
        ]);

        TahunAjaran::create($request->only('nama', 'tahun_mulai', 'tahun_selesai'));

        return redirect()->route('superadmin.master.tahun-ajaran')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function setAktifTahunAjaran(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->setAsAktif();
        return redirect()->route('superadmin.master.tahun-ajaran')
            ->with('success', "Tahun ajaran {$tahunAjaran->nama} ditetapkan sebagai aktif.");
    }

    public function destroyTahunAjaran(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->is_aktif) {
            return redirect()->route('superadmin.master.tahun-ajaran')
                ->with('error', 'Tidak bisa menghapus tahun ajaran yang sedang aktif.');
        }
        $tahunAjaran->delete();
        return redirect()->route('superadmin.master.tahun-ajaran')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    // ============== JURUSAN ==============

    public function jurusan()
    {
        $jurusans = Jurusan::with('kaprogGuru')->orderBy('kode')->get();
        $gurus    = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();
        return view('admin.master.jurusan', compact('jurusans', 'gurus'));
    }

    public function storeJurusan(Request $request)
    {
        $request->validate([
            'kode'          => 'required|string|max:10|unique:jurusans,kode',
            'nama'          => 'required|string|max:255',
            'singkatan'     => 'required|string|max:10',
            'kaprog_id'     => 'nullable|exists:users,id',
            'ketua_jurusan' => 'nullable|string|max:255',
        ], [
            'kode.unique' => 'Kode jurusan sudah digunakan.',
        ]);

        $data = $request->only('kode', 'nama', 'singkatan', 'kaprog_id', 'ketua_jurusan');
        $data['is_aktif'] = true;

        if ($request->filled('kaprog_id')) {
            $guru = User::find($request->kaprog_id);
            if ($guru) {
                $data['ketua_jurusan'] = $guru->name;
            }
        }

        Jurusan::create($data);

        return redirect()->route('superadmin.master.jurusan')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function updateJurusan(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'kode'          => 'required|string|max:10|unique:jurusans,kode,' . $jurusan->id,
            'nama'          => 'required|string|max:255',
            'singkatan'     => 'required|string|max:10',
            'kaprog_id'     => 'nullable|exists:users,id',
            'ketua_jurusan' => 'nullable|string|max:255',
        ], [
            'kode.unique' => 'Kode jurusan sudah digunakan.',
        ]);

        $data = $request->only('kode', 'nama', 'singkatan', 'kaprog_id', 'ketua_jurusan');

        if ($request->filled('kaprog_id')) {
            $guru = User::find($request->kaprog_id);
            if ($guru) {
                $data['ketua_jurusan'] = $guru->name;
            }
        }

        $jurusan->update($data);

        return redirect()->route('superadmin.master.jurusan')
            ->with('success', 'Data jurusan berhasil diperbarui.');
    }

    public function destroyJurusan(Jurusan $jurusan)
    {
        $jurusan->delete();
        return redirect()->route('superadmin.master.jurusan')
            ->with('success', 'Jurusan berhasil dihapus.');
    }

    // ============== KELAS ==============

    public function kelas(Request $request)
    {
        $query = Kelas::with(['jurusan', 'tahunAjaran', 'waliKelasGuru']);

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }
        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        $kelas        = $query->orderBy('tingkat')->orderBy('nama')->paginate(20)->withQueryString();
        $jurusans     = Jurusan::where('is_aktif', true)->orderBy('nama')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('tahun_mulai')->get();
        $gurus        = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();

        return view('admin.master.kelas', compact('kelas', 'jurusans', 'tahunAjarans', 'gurus'));
    }

    public function storeKelas(Request $request)
    {
        $request->validate([
            'jurusan_id'      => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'tingkat'         => 'required|in:X,XI,XII',
            'nama_kelas'      => 'required|string|max:50',
            'wali_kelas_id'   => 'nullable|exists:users,id',
            'wali_kelas'      => 'nullable|string|max:255',
        ]);

        $data = $request->only('jurusan_id', 'tahun_ajaran_id', 'tingkat', 'nama_kelas', 'wali_kelas_id', 'wali_kelas');
        $data['nama']       = $request->nama_kelas;
        $data['nama_kelas'] = $request->nama_kelas;
        $data['is_aktif']   = true;

        if ($request->filled('wali_kelas_id')) {
            $guru = User::find($request->wali_kelas_id);
            if ($guru) {
                $data['wali_kelas'] = $guru->name;
            }
        }

        $kelas = Kelas::create($data);

        return redirect()->route('superadmin.master.kelas')
            ->with('success', "Kelas {$kelas->nama_kelas} berhasil ditambahkan dan jabatan wali kelas telah disinkronkan.");
    }

    public function updateKelas(Request $request, Kelas $kela)
    {
        $request->validate([
            'jurusan_id'      => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'tingkat'         => 'required|in:X,XI,XII',
            'nama_kelas'      => 'required|string|max:50',
            'wali_kelas_id'   => 'nullable|exists:users,id',
            'wali_kelas'      => 'nullable|string|max:255',
        ]);

        $data = $request->only('jurusan_id', 'tahun_ajaran_id', 'tingkat', 'nama_kelas', 'wali_kelas_id', 'wali_kelas');
        $data['nama']       = $request->nama_kelas;
        $data['nama_kelas'] = $request->nama_kelas;

        if ($request->filled('wali_kelas_id')) {
            $guru = User::find($request->wali_kelas_id);
            if ($guru) {
                $data['wali_kelas'] = $guru->name;
            }
        } else {
            $data['wali_kelas_id'] = null;
        }

        $kela->update($data);

        return redirect()->route('superadmin.master.kelas')
            ->with('success', "Data kelas {$kela->nama_kelas} berhasil diperbarui dan jabatan wali kelas telah disinkronkan.");
    }

    public function destroyKelas(Kelas $kela)
    {
        $nama = $kela->nama_kelas ?? $kela->nama;
        $kela->delete();
        return redirect()->route('superadmin.master.kelas')
            ->with('success', "Kelas {$nama} berhasil dihapus.");
    }

    // ============== TUGAS TAMBAHAN MASTER ==============

    public function tugasTambahan()
    {
        $tugasTambahanList = \App\Models\TugasTambahan::orderBy('nama')->get();
        return view('admin.master.tugas-tambahan', compact('tugasTambahanList'));
    }

    public function storeTugasTambahan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:master_tugas_tambahan,nama',
            'kategori' => 'required|string|max:50',
            'nominal_gaji' => 'required|numeric|min:0',
        ]);

        \App\Models\TugasTambahan::create([
            'nama'         => trim($request->nama),
            'kategori'     => $request->kategori,
            'nominal_gaji' => $request->nominal_gaji,
            'kode'         => $request->input('kode', null),
            'deskripsi'    => $request->input('deskripsi', null),
            'is_aktif'     => true,
        ]);

        return redirect()->route('superadmin.master.tugas-tambahan')
            ->with('success', 'Master Tugas Tambahan berhasil ditambahkan.');
    }

    public function updateTugasTambahan(Request $request, \App\Models\TugasTambahan $tugasTambahan)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:master_tugas_tambahan,nama,' . $tugasTambahan->id,
            'kategori' => 'required|string|max:50',
            'nominal_gaji' => 'required|numeric|min:0',
        ]);

        $tugasTambahan->update([
            'nama'         => trim($request->nama),
            'kategori'     => $request->kategori,
            'nominal_gaji' => $request->nominal_gaji,
            'kode'         => $request->input('kode', $tugasTambahan->kode),
            'deskripsi'    => $request->input('deskripsi', $tugasTambahan->deskripsi),
            'is_aktif'     => $request->has('is_aktif') ? (bool)$request->is_aktif : true,
        ]);

        return redirect()->route('superadmin.master.tugas-tambahan')
            ->with('success', 'Master Tugas Tambahan berhasil diperbarui.');
    }

    public function destroyTugasTambahan(\App\Models\TugasTambahan $tugasTambahan)
    {
        $nama = $tugasTambahan->nama;
        $tugasTambahan->delete();
        return redirect()->route('superadmin.master.tugas-tambahan')
            ->with('success', "Tugas tambahan {$nama} berhasil dihapus.");
    }

    // ============== PENGATURAN SEKOLAH MASTER ==============

    public function pengaturanSekolah()
    {
        $setting = \App\Models\PengaturanSekolah::getSetting();
        $gurus   = User::whereIn('role', ['guru', 'superadmin'])->orderBy('name')->get();
        return view('admin.master.sekolah', compact('setting', 'gurus'));
    }

    public function updatePengaturanSekolah(Request $request)
    {
        $request->validate([
            'nama_sekolah'      => 'required|string|max:200',
            'npsn'              => 'nullable|string|max:50',
            'kepala_sekolah_id' => 'nullable|exists:users,id',
            'alamat'            => 'nullable|string',
            'email'             => 'nullable|email|max:150',
            'telepon'           => 'nullable|string|max:50',
            'website'           => 'nullable|string|max:150',
        ]);

        $setting = \App\Models\PengaturanSekolah::getSetting();
        $setting->update($request->only('nama_sekolah', 'npsn', 'kepala_sekolah_id', 'alamat', 'email', 'telepon', 'website'));

        return redirect()->route('superadmin.master.sekolah')
            ->with('success', 'Pengaturan sekolah & penugasan Kepala Sekolah berhasil diperbarui.');
    }

    // ============== EXPORT ALL MASTER DATA ==============

    public function export()
    {
        return Excel::download(new MasterDataExport(), 'master_data_' . date('Ymd_His') . '.xlsx');
    }

    // ============== IMPORT MASTER DATA ==============

    public function importForm()
    {
        return view('admin.master.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'Format file harus .xlsx atau .xls',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $import = new MasterDataImport();
            Excel::import($import, $request->file('file'));

            $summary = $import->getSummary();
            $msg = "Import selesai! Jurusan: {$summary['jurusan']}, Kelas: {$summary['kelas']}, Tahun Ajaran: {$summary['tahun_ajaran']} data diproses.";

            return redirect()->route('superadmin.master.jurusan')
                ->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('superadmin.master.jurusan')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ============== DOWNLOAD TEMPLATE ==============

    public function downloadTemplate()
    {
        // Multi-sheet template via MasterDataExport with empty data placeholder
        $export = new TemplateExport(
            'Template Master Data',
            ['Kode', 'Nama Jurusan', 'Singkatan', 'Ketua Jurusan'],
            [['TKJT', 'Teknik Komputer dan Jaringan Telekomunikasi', 'TKJT', 'Moh. Roghib, S.Kom']],
            'FFC0392B'
        );
        return Excel::download($export, 'template_master_data.xlsx');
    }
}
