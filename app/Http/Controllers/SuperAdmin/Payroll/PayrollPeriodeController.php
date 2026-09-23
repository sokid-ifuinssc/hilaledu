<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollPeriode;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollItem;
use App\Models\Payroll\PayrollSetting;
use App\Models\PengaturanSekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayrollPeriodeController extends Controller
{
    public function index()
    {
        $periodes = PayrollPeriode::withCount('payrolls')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->paginate(12);

        return view('superadmin.payroll.periode.index', compact('periodes'));
    }

    public function create()
    {
        $bulanList = [];
        for ($b = 1; $b <= 12; $b++) {
            $bulanList[$b] = PayrollPeriode::getNamaBulan($b);
        }

        $defaultBulan = (int) date('n');
        $defaultTahun = (int) date('Y');

        return view('superadmin.payroll.periode.create', compact('bulanList', 'defaultBulan', 'defaultTahun'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan'              => 'required|integer|between:1,12',
            'tahun'              => 'required|integer|min:2020|max:2099',
            'nama_periode'       => 'nullable|string|max:100',
            'tanggal_mulai'      => 'nullable|date',
            'tanggal_selesai'    => 'nullable|date|after_or_equal:tanggal_mulai',
            'tanggal_pembayaran' => 'nullable|date',
            'catatan'            => 'nullable|string',
        ]);

        // Cek duplikasi periode
        $exists = PayrollPeriode::where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['bulan' => 'Periode untuk bulan dan tahun tersebut sudah ada.']);
        }

        if (empty($validated['nama_periode'])) {
            $validated['nama_periode'] = PayrollPeriode::getNamaBulan($validated['bulan']) . ' ' . $validated['tahun'];
        }

        $validated['status']     = 'draft';
        $validated['created_by'] = auth()->id();

        $periode = PayrollPeriode::create($validated);

        return redirect()->route('superadmin.payroll.periode.show', $periode)
            ->with('success', "Periode '{$periode->nama_periode}' berhasil dibuat. Silakan klik 'Hitung Gaji Otomatis' untuk memproses gaji pegawai.");
    }

    public function show(PayrollPeriode $periode, Request $request)
    {
        $query = $periode->payrolls()->with(['user', 'user.payrollSetting', 'items']);

        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->join('users', 'payrolls.user_id', '=', 'users.id')
            ->orderBy('users.role')
            ->orderBy('users.name')
            ->select('payrolls.*')
            ->paginate(15)
            ->withQueryString();

        $countGuru   = $periode->payrolls()->whereHas('user', fn($q) => $q->where('role', 'guru'))->count();
        $countTendik = $periode->payrolls()->whereHas('user', fn($q) => $q->where('role', 'tendik'))->count();

        return view('superadmin.payroll.periode.show', compact('periode', 'payrolls', 'countGuru', 'countTendik'));
    }

    /**
     * Hitung & Generate Otomatis Gaji Semua Guru & Tendik Aktif
     */
    public function generate(PayrollPeriode $periode)
    {
        if ($periode->status === 'paid') {
            return back()->with('error', 'Periode yang sudah berstatus Dibayarkan (Paid) tidak dapat dihitung ulang.');
        }

        $pegawais = User::whereIn('role', ['guru', 'tendik'])
            ->where('is_active', true)
            ->with('payrollSetting')
            ->get();

        $generatedCount = 0;

        DB::transaction(function () use ($periode, $pegawais, &$generatedCount) {
            foreach ($pegawais as $pegawai) {
                $setting = $pegawai->payrollSetting;
                if (!$setting) {
                    $isGuru = $pegawai->role === 'guru';
                    $setting = PayrollSetting::create([
                        'user_id'              => $pegawai->id,
                        'gaji_pokok'           => $isGuru ? 1500000 : 1800000,
                        'honor_per_jam'        => $isGuru ? 35000 : 0,
                        'jam_mengajar_default' => $isGuru ? 24 : 0,
                        'tunjangan_jabatan'    => 0,
                        'tunjangan_kehadiran'  => 200000,
                        'tunjangan_lain'       => 0,
                        'potongan_bpjs'        => 45000,
                        'potongan_koperasi'    => 50000,
                        'potongan_lain'        => 25000,
                        'atas_nama_rekening'   => $pegawai->name,
                    ]);
                }

                $nomorSlip = sprintf('SLIP/%04d/%02d/%04d', $periode->tahun, $periode->bulan, $pegawai->id);

                $payroll = Payroll::firstOrNew([
                    'payroll_periode_id' => $periode->id,
                    'user_id'            => $pegawai->id,
                ]);

                // --- 1. SINKRONISASI JAM MENGAJAR ---
                $jadwals = \App\Models\JadwalPelajaran::where('guru_user_id', $pegawai->id)->get();
                $jamMingguan = 0;
                foreach($jadwals as $j) {
                    $jamMingguan += ($j->jam_ke_selesai - $j->jam_ke_mulai + 1);
                }
                $totalJam = $jamMingguan * 4; // Asumsi 4 minggu per bulan

                // --- 2. SINKRONISASI KEHADIRAN ---
                $kehadiran = \App\Models\PresensiHarianGuru::where('guru_user_id', $pegawai->id)
                    ->whereMonth('tanggal', $periode->bulan)
                    ->whereYear('tanggal', $periode->tahun)
                    ->whereIn('status_masuk', ['Hadir', 'Terlambat'])
                    ->count();

                $payroll->nomor_slip          = $nomorSlip;
                $payroll->gaji_pokok          = $setting->gaji_pokok;
                $payroll->jumlah_jam_mengajar = $totalJam > 0 ? $totalJam : $setting->jam_mengajar_default;
                $payroll->jumlah_kehadiran    = $kehadiran;
                $payroll->status              = $payroll->status ?? 'draft';
                $payroll->metode_pembayaran   = !empty($setting->nomor_rekening) ? 'transfer' : 'tunai';
                $payroll->save();

                // Hapus item lama jika ada perhitungan ulang
                $payroll->items()->delete();

                // 1. Tambah Penerimaan
                // Gaji Pokok
                if ($setting->gaji_pokok > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Gaji Pokok',
                        'jenis'         => 'penerimaan',
                        'nominal'       => $setting->gaji_pokok,
                        'keterangan'    => 'Gaji pokok bulanan',
                    ]);
                }

                // Honor Jam Mengajar (khusus guru)
                $honorJam = (float) $setting->honor_per_jam * (int) $payroll->jumlah_jam_mengajar;
                if ($honorJam > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Honor Jam Mengajar',
                        'jenis'         => 'penerimaan',
                        'nominal'       => $honorJam,
                        'keterangan'    => "{$payroll->jumlah_jam_mengajar} Jam x Rp " . number_format($setting->honor_per_jam, 0, ',', '.'),
                    ]);
                    $payroll->total_honor_jam = $honorJam;
                }

                // Tunjangan Tugas Tambahan (Sinkronisasi dari Master Akademik)
                $daftarTugas = $pegawai->daftar_jabatan; // get from mutators (which looks at users.tugas_tambahan)
                if (is_array($daftarTugas) && count($daftarTugas) > 0) {
                    foreach ($daftarTugas as $tugas) {
                        // Cari default nominal dari Master Tugas Tambahan
                        $komp = \App\Models\TugasTambahan::where('nama', $tugas)->where('is_aktif', true)->first();
                        $nominalTugas = $komp ? $komp->nominal_gaji : 0;
                        
                        PayrollItem::create([
                            'payroll_id'    => $payroll->id,
                            'nama_komponen' => 'Tugas Tambahan: ' . $tugas,
                            'jenis'         => 'penerimaan',
                            'nominal'       => $nominalTugas,
                            'keterangan'    => 'Kategori: ' . ($komp ? $komp->kategori : 'Umum'),
                        ]);
                    }
                } elseif ($setting->tunjangan_jabatan > 0) {
                    // Fallback jika tidak ada tugas_tambahan spesifik namun ada setting
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Tunjangan Jabatan',
                        'jenis'         => 'penerimaan',
                        'nominal'       => $setting->tunjangan_jabatan,
                        'keterangan'    => 'Tunjangan jabatan default',
                    ]);
                }
                // Tunjangan Kehadiran / Transport
                if ($setting->tunjangan_kehadiran > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Tunjangan Kehadiran & Transport',
                        'jenis'         => 'penerimaan',
                        'nominal'       => $setting->tunjangan_kehadiran,
                        'keterangan'    => 'Uang transport dan kehadiran',
                    ]);
                }

                // Tunjangan Lain
                if ($setting->tunjangan_lain > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Tunjangan Lainnya',
                        'jenis'         => 'penerimaan',
                        'nominal'       => $setting->tunjangan_lain,
                        'keterangan'    => 'Tunjangan insentif lainnya',
                    ]);
                }

                // 2. Tambah Potongan
                // Potongan BPJS
                if ($setting->potongan_bpjs > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'BPJS Ketenagakerjaan',
                        'jenis'         => 'potongan',
                        'nominal'       => $setting->potongan_bpjs,
                        'keterangan'    => 'Iuran BPJS',
                    ]);
                }

                // Potongan Koperasi
                if ($setting->potongan_koperasi > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Koperasi Sekolah',
                        'jenis'         => 'potongan',
                        'nominal'       => $setting->potongan_koperasi,
                        'keterangan'    => 'Simpanan koperasi SMK Plus Al Hilal',
                    ]);
                }

                // Potongan Infaq / Kas
                if ($setting->potongan_lain > 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => 'Infaq & Kas Sosial Yayasan',
                        'jenis'         => 'potongan',
                        'nominal'       => $setting->potongan_lain,
                        'keterangan'    => 'Infaq yayasan',
                    ]);
                }

                // Hitung total penerimaan, potongan, take home pay
                $payroll->total_tunjangan = (float) $setting->tunjangan_jabatan + (float) $setting->tunjangan_kehadiran + (float) $setting->tunjangan_lain;
                $payroll->recalculateTotals();

                $generatedCount++;
            }

            // Hitung akumulasi periode
            $periode->recalculateTotals();
        });

        return redirect()->route('superadmin.payroll.periode.show', $periode)
            ->with('success', "Berhasil menghitung dan membuat slip gaji untuk {$generatedCount} Guru & Tendik.");
    }

    /**
     * Edit satu slip gaji individu (misal ada penambahan bonus atau potongan insidentil)
     */
    public function editSlip(PayrollPeriode $periode, Payroll $payroll)
    {
        $payroll->load(['user', 'items']);
        return view('superadmin.payroll.periode.edit-slip', compact('periode', 'payroll'));
    }

    public function updateSlip(Request $request, PayrollPeriode $periode, Payroll $payroll)
    {
        $validated = $request->validate([
            'metode_pembayaran' => 'required|in:transfer,tunai',
            'status'            => 'required|in:draft,approved,paid',
            'catatan'           => 'nullable|string',
            'items'             => 'nullable|array',
            'items.*.nama'      => 'required|string|max:150',
            'items.*.jenis'     => 'required|in:penerimaan,potongan',
            'items.*.nominal'   => 'required|numeric|min:0',
            'items.*.ket'       => 'nullable|string|max:255',
        ]);

        $payroll->metode_pembayaran = $validated['metode_pembayaran'];
        $payroll->status            = $validated['status'];
        $payroll->catatan           = $validated['catatan'];
        $payroll->save();

        // Update items
        $payroll->items()->delete();
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if ((float) $item['nominal'] >= 0) {
                    PayrollItem::create([
                        'payroll_id'    => $payroll->id,
                        'nama_komponen' => $item['nama'],
                        'jenis'         => $item['jenis'],
                        'nominal'       => $item['nominal'],
                        'keterangan'    => $item['ket'] ?? null,
                    ]);
                }
            }
        }

        $payroll->recalculateTotals();

        return redirect()->route('superadmin.payroll.periode.show', $periode)
            ->with('success', "Slip gaji untuk {$payroll->user->name} berhasil diperbarui.");
    }

    /**
     * Finalisasi Periode
     */
    public function finalize(PayrollPeriode $periode)
    {
        $periode->update(['status' => 'finalized']);
        $periode->payrolls()->update(['status' => 'approved']);

        return back()->with('success', "Periode '{$periode->nama_periode}' berhasil difinalisasi.");
    }

    /**
     * Tandai sudah dibayar (Paid)
     */
    public function markPaid(Request $request, PayrollPeriode $periode)
    {
        $tglBayar = $request->input('tanggal_pembayaran', date('Y-m-d'));

        $periode->update([
            'status'             => 'paid',
            'tanggal_pembayaran' => $tglBayar,
        ]);

        $periode->payrolls()->update([
            'status'          => 'paid',
            'tanggal_dibayar' => $tglBayar,
        ]);

        return back()->with('success', "Periode '{$periode->nama_periode}' berhasil ditandai telah dibayarkan.");
    }

    /**
     * Tampilan Cetak Slip Gaji Resmi
     */
    public function printSlip(PayrollPeriode $periode, Payroll $payroll)
    {
        $payroll->load(['user', 'items', 'user.payrollSetting']);
        $sekolah = PengaturanSekolah::getSetting();

        return view('superadmin.payroll.periode.slip', compact('periode', 'payroll', 'sekolah'));
    }

    /**
     * Tampilan Cetak Rekapitulasi Penggajian Periode (Laporan untuk Yayasan/Kepsek)
     */
    public function printRekap(PayrollPeriode $periode)
    {
        $payrolls = $periode->payrolls()
            ->with(['user', 'user.payrollSetting'])
            ->join('users', 'payrolls.user_id', '=', 'users.id')
            ->orderBy('users.role')
            ->orderBy('users.name')
            ->select('payrolls.*')
            ->get();

        $sekolah = PengaturanSekolah::getSetting();

        return view('superadmin.payroll.periode.rekap', compact('periode', 'payrolls', 'sekolah'));
    }

    /**
     * Hapus Periode
     */
    public function destroy(PayrollPeriode $periode)
    {
        $nama = $periode->nama_periode;
        $periode->delete();

        return redirect()->route('superadmin.payroll.periode.index')
            ->with('success', "Periode '{$nama}' berhasil dihapus.");
    }
}
