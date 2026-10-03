<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollKomponen;
use App\Models\Payroll\PayrollSetting;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollSettingController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['guru', 'tendik'])->with('payrollSetting');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $pegawais = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        $totalGuru   = User::where('role', 'guru')->count();
        $totalTendik = User::where('role', 'tendik')->count();

        return view('superadmin.payroll.setting.index', compact('pegawais', 'totalGuru', 'totalTendik'));
    }

    public function edit(User $user)
    {
        if (!in_array($user->role, ['guru', 'tendik'])) {
            abort(404, 'Pengguna bukan guru atau tendik.');
        }

        $isGuru = $user->role === 'guru';
        $jamPenugasan = $isGuru ? $user->total_jam_mengajar : 0;
        $hadirBulanIni = $isGuru ? $user->hari_hadir_bulan_ini : 0;

        // Ambil nilai default dari Master Komponen Gaji yang aktif
        $masterHonorJam = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('tipe', 'per_jam')
                  ->orWhere('kode', 'HJM01')
                  ->orWhere('nama', 'like', '%jam%mengajar%')
                  ->orWhere('nama', 'like', '%honor%jam%');
            })->value('nominal_default') ?? 35000);

        $masterTransport = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('tipe', 'per_kehadiran')
                  ->orWhere('kode', 'TK01')
                  ->orWhere('nama', 'like', '%transport%');
            })->value('nominal_default') ?? 20000);

        $masterGajiPokok = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('kode', 'GP01')
                  ->orWhere('nama', 'like', '%pokok%');
            })->value('nominal_default') ?? 1800000);

        $masterTunjanganKehadiran = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('kode', 'TK01')
                  ->orWhere('nama', 'like', '%kehadiran%');
            })->value('nominal_default') ?? 250000);

        $masterBpjs = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('kode', 'PBP01')
                  ->orWhere('nama', 'like', '%bpjs%');
            })->value('nominal_default') ?? 45000);

        $masterKoperasi = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('kode', 'PKOP01')
                  ->orWhere('nama', 'like', '%koperasi%');
            })->value('nominal_default') ?? 50000);

        $masterPotonganLain = (float) (PayrollKomponen::where('is_aktif', true)
            ->where(function($q) {
                $q->where('kode', 'PINF01')
                  ->orWhere('nama', 'like', '%infaq%')
                  ->orWhere('nama', 'like', '%kas%');
            })->value('nominal_default') ?? 25000);

        // Ambil seluruh master komponen untuk pemetaan tugas tambahan
        $allMaster = PayrollKomponen::where('is_aktif', true)->get();
        $masterKomponenMap = [];
        foreach ($allMaster as $mk) {
            $masterKomponenMap[trim(mb_strtolower($mk->nama))] = (float) $mk->nominal_default;
        }
        $allTugas = \App\Models\TugasTambahan::where('is_aktif', true)->get();
        foreach ($allTugas as $tt) {
            $k = trim(mb_strtolower($tt->nama));
            if (!isset($masterKomponenMap[$k]) || $masterKomponenMap[$k] == 0) {
                $masterKomponenMap[$k] = (float) $tt->nominal_gaji;
            }
        }

        $setting = $user->payrollSetting;
        if (!$setting) {
            $setting = new PayrollSetting([
                'user_id'                => $user->id,
                'gaji_pokok'             => $isGuru ? 0 : $masterGajiPokok,
                'honor_per_jam'          => $isGuru ? $masterHonorJam : 0,
                'jam_mengajar_default'   => $jamPenugasan ?: ($isGuru ? 24 : 0),
                'tunjangan_jabatan'      => 0,
                'detail_tunjangan_tugas' => [],
                'tunjangan_kehadiran'    => $isGuru ? 0 : $masterTunjanganKehadiran,
                'transport_per_hari'     => $masterTransport,
                'hari_transport_default' => 0, // Dihitung dinamis dari presensi riil harian
                'tunjangan_lain'         => 0,
                'potongan_bpjs'          => $masterBpjs,
                'potongan_koperasi'      => $masterKoperasi,
                'potongan_lain'          => $masterPotonganLain,
                'atas_nama_rekening'     => $user->name,
            ]);
        } else {
            // Otomatis isi nilai master komponen jika pada setting guru belum diset (>0) atau bernilai 0
            if ($isGuru && ((float)$setting->honor_per_jam <= 0)) {
                $setting->honor_per_jam = $masterHonorJam;
            }
            if ((float)$setting->transport_per_hari <= 0) {
                $setting->transport_per_hari = $masterTransport;
            }
            if ((float)$setting->potongan_bpjs <= 0 && $masterBpjs > 0) {
                $setting->potongan_bpjs = $masterBpjs;
            }
            if ((float)$setting->potongan_koperasi <= 0 && $masterKoperasi > 0) {
                $setting->potongan_koperasi = $masterKoperasi;
            }
            if ((float)$setting->potongan_lain <= 0 && $masterPotonganLain > 0) {
                $setting->potongan_lain = $masterPotonganLain;
            }
        }

        // Jika guru dan jam penugasan ditemukan di database, sinkronkan nilai default jam mengajar jika belum diset
        if ($isGuru && $jamPenugasan > 0 && !$setting->exists) {
            $setting->jam_mengajar_default = $jamPenugasan;
        }

        // Jangan default ke 16 hari! Biarkan 0 jika belum diset agar dihitung dinamis dari presensi riil
        if ($isGuru && !isset($setting->hari_transport_default)) {
            $setting->hari_transport_default = 0;
        }

        // Untuk guru, pastikan gaji pokok bernilai 0
        if ($isGuru) {
            $setting->gaji_pokok = 0;
        }

        // Dapatkan daftar tugas tambahan yang diemban (kecuali label dasar 'Guru' atau 'Tendik')
        $daftarTugas = collect($user->daftar_jabatan)
            ->reject(fn($t) => in_array($t, ['Guru', 'Tendik', 'Guru Pengajar', 'Siswa']))
            ->values();

        // Otomatis terisi nilai master komponen untuk setiap tugas tambahan yang diemban guru
        $detailTugas = is_array($setting->detail_tunjangan_tugas) ? $setting->detail_tunjangan_tugas : [];
        $hasNewDefault = false;
        foreach ($daftarTugas as $tugas) {
            $key = trim(mb_strtolower($tugas));
            $masterNominal = $masterKomponenMap[$key] ?? 0;
            // Jika belum ada di detail atau bernilai 0 tetapi di master komponen sudah ada nilainya:
            if ((!isset($detailTugas[$tugas]) || (float)$detailTugas[$tugas] <= 0) && $masterNominal > 0) {
                $detailTugas[$tugas] = $masterNominal;
                $hasNewDefault = true;
            }
        }
        if ($hasNewDefault) {
            $setting->detail_tunjangan_tugas = $detailTugas;
            $setting->tunjangan_jabatan = array_sum($detailTugas);
        }

        return view('superadmin.payroll.setting.edit', compact(
            'user', 
            'setting', 
            'daftarTugas', 
            'hadirBulanIni',
            'masterHonorJam', 
            'masterTransport', 
            'masterGajiPokok', 
            'masterTunjanganKehadiran', 
            'masterBpjs', 
            'masterKoperasi', 
            'masterPotonganLain', 
            'masterKomponenMap'
        ));
    }

    public function update(Request $request, User $user)
    {
        $isGuru = $user->role === 'guru';

        $rules = [
            'honor_per_jam'           => 'required|numeric|min:0',
            'jam_mengajar_default'    => 'required|integer|min:0',
            'transport_per_hari'      => 'nullable|numeric|min:0',
            'hari_transport_default'  => 'nullable|integer|min:0|max:31',
            'tunjangan_lain'          => 'nullable|numeric|min:0',
            'potongan_bpjs'           => 'nullable|numeric|min:0',
            'potongan_koperasi'       => 'nullable|numeric|min:0',
            'potongan_lain'           => 'nullable|numeric|min:0',
            'rekening_bank'           => 'nullable|string|max:100',
            'nomor_rekening'          => 'nullable|string|max:60',
            'atas_nama_rekening'      => 'nullable|string|max:150',
            'catatan'                 => 'nullable|string',
            'tugas_tambahan_nominal'  => 'nullable|array',
            'custom_tugas_nama'       => 'nullable|array',
            'custom_tugas_nominal'    => 'nullable|array',
        ];

        if (!$isGuru) {
            $rules['gaji_pokok']          = 'required|numeric|min:0';
            $rules['tunjangan_kehadiran'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Jika guru, gaji pokok ditiadakan (0), diambil dari jam mengajar x honor
        if ($isGuru) {
            $validated['gaji_pokok'] = 0;
            $validated['tunjangan_kehadiran'] = 0;
        } else {
            $validated['gaji_pokok'] = $validated['gaji_pokok'] ?? 0;
            $validated['tunjangan_kehadiran'] = $validated['tunjangan_kehadiran'] ?? 0;
        }

        // Proses rincian nominal per tugas tambahan
        $detailTugas = [];
        $totalTunjanganJabatan = 0;

        // 1. Tugas terdaftar
        if (!empty($request->tugas_tambahan_nominal) && is_array($request->tugas_tambahan_nominal)) {
            foreach ($request->tugas_tambahan_nominal as $namaTugas => $nominal) {
                $nomFloat = max(0, (float) $nominal);
                $detailTugas[$namaTugas] = $nomFloat;
                $totalTunjanganJabatan += $nomFloat;
            }
        }

        // 2. Tugas kustom tambahan (jika ada input baru dari form)
        if (!empty($request->custom_tugas_nama) && is_array($request->custom_tugas_nama)) {
            foreach ($request->custom_tugas_nama as $idx => $namaKustom) {
                $namaKustom = trim($namaKustom);
                if (!empty($namaKustom)) {
                    $nomKustom = isset($request->custom_tugas_nominal[$idx]) ? max(0, (float)$request->custom_tugas_nominal[$idx]) : 0;
                    $detailTugas[$namaKustom] = $nomKustom;
                    $totalTunjanganJabatan += $nomKustom;
                }
            }
        }

        $validated['detail_tunjangan_tugas'] = $detailTugas;
        $validated['tunjangan_jabatan']      = $totalTunjanganJabatan;
        $validated['transport_per_hari']     = $validated['transport_per_hari'] ?? 20000;
        $validated['hari_transport_default'] = isset($validated['hari_transport_default']) ? (int) $validated['hari_transport_default'] : 0;
        $validated['tunjangan_lain']         = $validated['tunjangan_lain'] ?? 0;
        $validated['potongan_bpjs']          = $validated['potongan_bpjs'] ?? 0;
        $validated['potongan_koperasi']      = $validated['potongan_koperasi'] ?? 0;
        $validated['potongan_lain']          = $validated['potongan_lain'] ?? 0;

        PayrollSetting::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()->route('superadmin.payroll.setting.index')
            ->with('success', "Pengaturan gaji untuk {$user->name} berhasil diperbarui.");
    }

    /**
     * Hitung otomatis & sinkronkan tunjangan jabatan berdasarkan data tugas tambahan guru saat ini.
     */
    public function syncTugasTambahan()
    {
        $pegawais = User::whereIn('role', ['guru', 'tendik'])->get();
        $updated = 0;

        foreach ($pegawais as $p) {
            PayrollSetting::syncTunjanganForUser($p);
            $updated++;
        }

        return redirect()->route('superadmin.payroll.setting.index')
            ->with('success', "Berhasil mensinkronkan tunjangan jabatan untuk {$updated} pendidik & tendik.");
    }
}
