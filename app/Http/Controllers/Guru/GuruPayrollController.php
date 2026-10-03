<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Payroll;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;

class GuruPayrollController extends Controller
{
    /**
     * Tampilkan riwayat slip gaji guru yang sedang login (Riwayat Transaksi)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Sinkronkan payroll guru hanya untuk periode yang diizinkan tampil ke guru
        $this->syncGuruPayrolls($user);

        $query = Payroll::where('user_id', $user->id)
            ->with(['periode', 'items'])
            ->join('payroll_periodes', 'payrolls.payroll_periode_id', '=', 'payroll_periodes.id')
            ->where('payroll_periodes.tampil_ke_guru', true)
            ->orderByDesc('payroll_periodes.tahun')
            ->orderByDesc('payroll_periodes.bulan')
            ->select('payrolls.*');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('payroll_periodes.nama_periode', 'like', "%{$search}%")
                  ->orWhere('payroll_periodes.tahun', 'like', "%{$search}%")
                  ->orWhere('payrolls.status', 'like', "%{$search}%")
                  ->orWhere('payrolls.nomor_slip', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $payrolls = $query->paginate($perPage)->withQueryString();

        $setting = $user->payrollSetting;

        $totalDiterima = Payroll::where('user_id', $user->id)
            ->whereIn('payrolls.status', ['approved', 'paid'])
            ->join('payroll_periodes', 'payrolls.payroll_periode_id', '=', 'payroll_periodes.id')
            ->where('payroll_periodes.tampil_ke_guru', true)
            ->sum('gaji_bersih');

        return view('guru.payroll.index', compact('payrolls', 'setting', 'totalDiterima'));
    }

    /**
     * Buatkan atau sinkronkan slip gaji guru untuk setiap periode yang diizinkan tampil
     * Hari hadir mengajar dihitung secara DINAMIS berdasarkan data absensi harian riil
     */
    private function syncGuruPayrolls($user): void
    {
        $setting = $user->payrollSetting;
        if (!$setting) {
            $setting = \App\Models\Payroll\PayrollSetting::syncTunjanganForUser($user);
        }

        $periodes = \App\Models\Payroll\PayrollPeriode::where('tampil_ke_guru', true)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        foreach ($periodes as $periode) {
            $payroll = Payroll::firstOrNew([
                'payroll_periode_id' => $periode->id,
                'user_id'            => $user->id,
            ]);

            // Hitung jam mengajar
            $jamMengajar = $user->total_jam_mengajar ?: ($setting?->jam_mengajar_default ?? 24);

            // Hitung hari hadir DINAMIS sesuai presensi riil guru di bulan ini
            $kehadiran = $user->getHariHadirBulan((int)$periode->bulan, (int)$periode->tahun);

            $masterHonorJam = (float) (\App\Models\Payroll\PayrollKomponen::where('is_aktif', true)
                ->where(function($q) {
                    $q->where('tipe', 'per_jam')
                      ->orWhere('kode', 'HJM01')
                      ->orWhere('nama', 'like', '%jam%mengajar%')
                      ->orWhere('nama', 'like', '%honor%jam%');
                })->value('nominal_default') ?? 35000);

            $masterTransport = (float) (\App\Models\Payroll\PayrollKomponen::where('is_aktif', true)
                ->where(function($q) {
                    $q->where('tipe', 'per_kehadiran')
                      ->orWhere('kode', 'TK01')
                      ->orWhere('nama', 'like', '%transport%');
                })->value('nominal_default') ?? 20000);

            $tarifHonor     = (float) (($setting?->honor_per_jam > 0) ? $setting->honor_per_jam : $masterHonorJam);
            $totalHonorJam  = $tarifHonor * $jamMengajar;
            $tarifTransport = (float) (($setting?->transport_per_hari > 0) ? $setting->transport_per_hari : $masterTransport);
            $totalTransport = $kehadiran * $tarifTransport; // 0 jika belum ada presensi

            if (!$payroll->exists) {
                $payroll->nomor_slip          = sprintf('SLIP/%04d/%02d/%04d', $periode->tahun, $periode->bulan, $user->id);
                $payroll->gaji_pokok          = 0;
                $payroll->jumlah_jam_mengajar = $jamMengajar;
                $payroll->jumlah_kehadiran    = $kehadiran;
                $payroll->total_honor_jam     = $totalHonorJam;
                $payroll->status              = $periode->status === 'paid' ? 'paid' : 'draft';
                $payroll->metode_pembayaran   = !empty($setting?->nomor_rekening) ? 'transfer' : 'tunai';
                $payroll->save();

                // Buat item penerimaan: Honor Jam Mengajar
                \App\Models\Payroll\PayrollItem::create([
                    'payroll_id'    => $payroll->id,
                    'nama_komponen' => 'Honor Jam Mengajar',
                    'jenis'         => 'penerimaan',
                    'nominal'       => $totalHonorJam,
                    'keterangan'    => "{$jamMengajar} Jam x Rp " . number_format($tarifHonor, 0, ',', '.'),
                ]);

                // Buat item penerimaan: Uang Transport Kehadiran (selalu tampilkan agar guru tahu tarif transport per hari)
                \App\Models\Payroll\PayrollItem::create([
                    'payroll_id'    => $payroll->id,
                    'nama_komponen' => 'Uang Transport Kehadiran / KBM',
                    'jenis'         => 'penerimaan',
                    'nominal'       => $totalTransport,
                    'keterangan'    => "{$kehadiran} Hari Hadir Mengajar x Rp " . number_format($tarifTransport, 0, ',', '.'),
                ]);

                // Tunjangan tugas tambahan
                $detailTugas = $setting?->detail_tunjangan_tugas ?? [];
                if (is_array($detailTugas)) {
                    foreach ($detailTugas as $namaTugas => $nom) {
                        $nomFloat = (float) $nom;
                        if ($nomFloat > 0) {
                            \App\Models\Payroll\PayrollItem::create([
                                'payroll_id'    => $payroll->id,
                                'nama_komponen' => 'Tugas Tambahan: ' . $namaTugas,
                                'jenis'         => 'penerimaan',
                                'nominal'       => $nomFloat,
                                'keterangan'    => 'Tunjangan tugas tambahan rutin',
                            ]);
                        }
                    }
                }

                // Potongan
                if (($setting?->potongan_bpjs ?? 0) > 0) {
                    \App\Models\Payroll\PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'BPJS Ketenagakerjaan',
                        'jenis'         => 'potongan',
                        'nominal'       => (float) $setting->potongan_bpjs,
                        'keterangan'    => 'Iuran BPJS',
                    ]);
                }
                if (($setting?->potongan_koperasi ?? 0) > 0) {
                    \App\Models\Payroll\PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Koperasi Sekolah',
                        'jenis'         => 'potongan',
                        'nominal'       => (float) $setting->potongan_koperasi,
                        'keterangan'    => 'Simpanan koperasi SMK Plus Al Hilal',
                    ]);
                }
                if (($setting?->potongan_lain ?? 0) > 0) {
                    \App\Models\Payroll\PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Infaq & Kas Sosial Yayasan',
                        'jenis'         => 'potongan',
                        'nominal'       => (float) $setting->potongan_lain,
                        'keterangan'    => 'Infaq yayasan',
                    ]);
                }

                $payroll->total_tunjangan = (float) $payroll->items()->where('jenis', 'penerimaan')->whereNotIn('nama_komponen', ['Gaji Pokok', 'Honor Jam Mengajar'])->sum('nominal');
                $payroll->recalculateTotals();
            } else {
                // Jika payroll draft, perbarui kehadiran dan tarif transport/honor secara dinamis!
                if ($payroll->status === 'draft') {
                    $payroll->jumlah_jam_mengajar = $jamMengajar;
                    $payroll->jumlah_kehadiran    = $kehadiran;
                    $payroll->total_honor_jam     = $totalHonorJam;
                    $payroll->save();

                    // Perbarui item Honor Jam Mengajar
                    $honorItem = $payroll->items()->where('nama_komponen', 'Honor Jam Mengajar')->first();
                    if ($honorItem) {
                        $honorItem->update([
                            'nominal'    => $totalHonorJam,
                            'keterangan' => "{$jamMengajar} Jam x Rp " . number_format($tarifHonor, 0, ',', '.'),
                        ]);
                    } else {
                        \App\Models\Payroll\PayrollItem::create([
                            'payroll_id'    => $payroll->id,
                            'nama_komponen' => 'Honor Jam Mengajar',
                            'jenis'         => 'penerimaan',
                            'nominal'       => $totalHonorJam,
                            'keterangan'    => "{$jamMengajar} Jam x Rp " . number_format($tarifHonor, 0, ',', '.'),
                        ]);
                    }

                    // Perbarui item transport (selalu tampilkan)
                    $transportItem = $payroll->items()->where('nama_komponen', 'like', '%Transport%')->first();
                    if ($transportItem) {
                        $transportItem->update([
                            'nominal'    => $totalTransport,
                            'keterangan' => "{$kehadiran} Hari Hadir Mengajar x Rp " . number_format($tarifTransport, 0, ',', '.'),
                        ]);
                    } else {
                        \App\Models\Payroll\PayrollItem::create([
                            'payroll_id'    => $payroll->id,
                            'nama_komponen' => 'Uang Transport Kehadiran / KBM',
                            'jenis'         => 'penerimaan',
                            'nominal'       => $totalTransport,
                            'keterangan'    => "{$kehadiran} Hari Hadir Mengajar x Rp " . number_format($tarifTransport, 0, ',', '.'),
                        ]);
                    }

                    $payroll->total_tunjangan = (float) $payroll->items()->where('jenis', 'penerimaan')->whereNotIn('nama_komponen', ['Gaji Pokok', 'Honor Jam Mengajar'])->sum('nominal');
                    $payroll->recalculateTotals();
                }
            }
        }
    }

    /**
     * Cetak slip gaji guru
     */
    public function print(Payroll $payroll)
    {
        if ($payroll->user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak melihat slip gaji orang lain.');
        }

        if ($payroll->periode && !$payroll->periode->tampil_ke_guru) {
            abort(403, 'Periode slip gaji ini sedang disembunyikan atau belum dipublikasikan oleh pengelola.');
        }

        $payroll->load(['periode', 'items', 'user', 'user.payrollSetting']);
        $sekolah = PengaturanSekolah::getSetting();

        return view('superadmin.payroll.periode.slip', [
            'periode' => $payroll->periode,
            'payroll' => $payroll,
            'sekolah' => $sekolah,
        ]);
    }
}
