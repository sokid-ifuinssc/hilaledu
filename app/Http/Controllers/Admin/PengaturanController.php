<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengaturanSekolah;
use App\Http\Controllers\Admin\JadwalMatrixController;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = PengaturanSekolah::getAllSettings();
        $guruList = JadwalMatrixController::getDaftarGuruResmi();
        $hilalAcademic = PengaturanSekolah::getHilalEduAcademicSetting();

        // Kategori keluhan default
        $kategoriList = $settings['kategori_keluhan'] ?? [
            'Penjelasan Materi',
            'Metode Mengajar',
            'Sarana Prasarana & Lab',
            'Sikap & Komunikasi Guru',
            'Penilaian & Tugas',
            'Kedisiplinan & Waktu KBM',
            'Lainnya',
        ];

        $allGurus = \App\Models\User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.pengaturan.index', compact('settings', 'guruList', 'kategoriList', 'allGurus', 'hilalAcademic'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_sekolah'         => 'required|string|max:150',
            'npsn'                 => 'nullable|string|max:20',
            'alamat_sekolah'       => 'required|string|max:255',
            'telepon_sekolah'      => 'nullable|string|max:50',
            'email_sekolah'        => 'nullable|email|max:100',
            'website_sekolah'      => 'nullable|string|max:100',
            'nama_kepala_sekolah'  => 'required|string|max:100',
            'nip_kepala_sekolah'   => 'nullable|string|max:50',
            'nama_waka_kurikulum'  => 'required|string|max:100',
            'nip_waka_kurikulum'   => 'nullable|string|max:50',
            'titimangsa'           => 'required|string|max:100',
            'tahun_pelajaran'      => 'required|string|max:30',
            'semester'             => 'required|string|max:20',
            'program_keahlian'     => 'nullable|string',
            'konsentrasi_keahlian' => 'nullable|string',
            'kategori_keluhan'     => 'nullable|array',
            'kategori_keluhan.*'   => 'nullable|string|max:100',
            'penugasan_guru'       => 'nullable|array',
        ]);

        foreach ($validated as $key => $val) {
            if ($key === 'kategori_keluhan') {
                $categories = array_values(array_unique(array_filter(array_map('trim', $val ?? []))));
                PengaturanSekolah::set($key, $categories, 'Daftar Kategori Masukan Siswa', 'json');
            } elseif ($key === 'penugasan_guru') {
                // Susun master penugasan guru berdasarkan kode 1 - 30
                $guruFormatted = [];
                foreach ($val as $kode => $info) {
                    $guruFormatted[(int)$kode] = [
                        'nama'    => trim($info['nama'] ?? ''),
                        'user_id' => !empty($info['user_id']) ? (int)$info['user_id'] : null,
                        'mapel'   => trim($info['mapel'] ?? ''),
                        'jabatan' => trim($info['jabatan'] ?? ''),
                    ];
                }
                PengaturanSekolah::set('master_penugasan_guru', $guruFormatted, 'Master Penugasan Guru & Kode Mengajar', 'json');
            } elseif ($key === 'tahun_pelajaran' || $key === 'semester') {
                // Disinkronkan secara terstruktur di bawah
            } else {
                PengaturanSekolah::set($key, $val);
            }
        }

        // Sinkronkan Tahun Pelajaran & Semester ke lokal dan ke database HilalEdu (tahun_ajarans & SSO)
        PengaturanSekolah::syncToHilalEdu($validated['tahun_pelajaran'], $validated['semester']);

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            PengaturanSekolah::set('logo_sekolah', 'images/' . $filename, 'Logo Resmi Sekolah', 'image');
        }

        return back()->with('success', 'Pengaturan sekolah, tahun akademik 2026/2027, kop jadwal, dan penugasan guru berhasil diperbarui!');
    }

    public function syncFromHilalEdu()
    {
        $success = PengaturanSekolah::syncFromHilalEdu();
        if ($success) {
            $ta = PengaturanSekolah::getActiveTahunAjaran();
            $smt = ucfirst(PengaturanSekolah::getActiveSemester());
            return back()->with('success', "Tahun Akademik ({$ta}) dan Semester ({$smt}) berhasil disinkronkan langsung dari database HilalEdu!");
        }
        return back()->with('error', 'Gagal menyinkronkan data dari database HilalEdu. Pastikan database HilalEdu online.');
    }

    public function resetPenugasanGuru()
    {
        $default = JadwalMatrixController::getDefaultGuruResmi();
        PengaturanSekolah::set('master_penugasan_guru', $default, 'Master Penugasan Guru & Kode Mengajar', 'json');

        return back()->with('success', 'Master Penugasan Guru & Kode Jadwal berhasil direset ke standar resmi sekolah!');
    }
}
