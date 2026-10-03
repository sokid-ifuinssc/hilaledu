<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Payroll\PayrollKomponen;
use App\Models\Payroll\PayrollPeriode;
use App\Models\Payroll\PayrollSetting;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayrollSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $guru;
    protected User $tendik;
    protected Application $hilalpay;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan akun Superadmin
        $this->superadmin = User::firstOrCreate(
            ['username' => 'superadmin_test'],
            [
                'name'         => 'Superadmin Test',
                'email'        => 'superadmin_test@hilaledu.sch.id',
                'password'     => bcrypt('password123'),
                'role'         => 'superadmin',
                'is_active'    => true,
                'nama_lengkap' => 'Superadmin Test',
            ]
        );

        // Siapkan akun Guru
        $this->guru = User::firstOrCreate(
            ['username' => 'guru_payroll_test'],
            [
                'name'           => 'Ahmad Fauzi, S.Pd',
                'email'          => 'ahmad.fauzi.test@hilaledu.sch.id',
                'password'       => bcrypt('password123'),
                'role'           => 'guru',
                'is_active'      => true,
                'nip'            => '198501012010011001',
                'nama_lengkap'   => 'Ahmad Fauzi, S.Pd',
                'tugas_tambahan' => ['Wali Kelas X RPL 1'],
            ]
        );

        // Siapkan akun Tendik
        $this->tendik = User::firstOrCreate(
            ['username' => 'tendik_payroll_test'],
            [
                'name'         => 'Siti Aminah, A.Md',
                'email'        => 'siti.aminah.test@hilaledu.sch.id',
                'password'     => bcrypt('password123'),
                'role'         => 'tendik',
                'is_active'    => true,
                'nip'          => '199002022015012002',
                'nama_lengkap' => 'Siti Aminah, A.Md',
            ]
        );

        // Siapkan aplikasi HilalPay
        $this->hilalpay = Application::updateOrCreate(
            ['slug' => 'hilalpay'],
            [
                'name'          => 'HilalPay',
                'description'   => 'Sistem Penggajian & Bisyarah terpadu',
                'icon'          => 'bi-cash-coin',
                'color'         => '#10b981',
                'url'           => 'http://localhost:8000/superadmin/payroll',
                'database_name' => 'db_hilaledu',
                'sso_enabled'   => true,
                'auto_sync'     => false,
                'status'        => 'active',
                'features'      => ['Gaji Pokok & Tunjangan', 'Slip Gaji Digital'],
                'sort_order'    => 6,
            ]
        );
    }

    public function test_superadmin_can_access_payroll_dashboard()
    {
        $response = $this->actingAs($this->superadmin)->get(route('superadmin.payroll.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('HilalPay');
        $response->assertSee('Riwayat Periode Penggajian');
    }

    public function test_superadmin_can_manage_master_komponen()
    {
        // 1. Create komponen
        $response = $this->actingAs($this->superadmin)->post(route('superadmin.payroll.komponen.store'), [
            'kode'            => 'TEST_KOMP_01',
            'nama'            => 'Tunjangan Uji Coba',
            'jenis'           => 'penerimaan',
            'tipe'            => 'tetap',
            'nominal_default' => 100000,
            'is_aktif'        => 1,
            'keterangan'      => 'Komponen untuk unit testing',
        ]);

        $response->assertRedirect(route('superadmin.payroll.komponen.index'));
        $this->assertDatabaseHas('payroll_komponens', [
            'kode' => 'TEST_KOMP_01',
            'nama' => 'Tunjangan Uji Coba',
        ]);

        $komponen = PayrollKomponen::where('kode', 'TEST_KOMP_01')->first();

        // 2. Toggle active
        $this->actingAs($this->superadmin)->patch(route('superadmin.payroll.komponen.toggle-active', $komponen));
        $this->assertFalse($komponen->fresh()->is_aktif);

        // 3. Delete
        $this->actingAs($this->superadmin)->delete(route('superadmin.payroll.komponen.destroy', $komponen));
        $this->assertDatabaseMissing('payroll_komponens', ['kode' => 'TEST_KOMP_01']);
    }

    public function test_superadmin_can_update_payroll_setting_for_guru()
    {
        $response = $this->actingAs($this->superadmin)->put(route('superadmin.payroll.setting.update', $this->guru), [
            'gaji_pokok'           => 2000000,
            'honor_per_jam'        => 40000,
            'jam_mengajar_default' => 24,
            'tunjangan_jabatan'    => 250000,
            'tunjangan_kehadiran'  => 200000,
            'tunjangan_lain'       => 50000,
            'potongan_bpjs'        => 45000,
            'potongan_koperasi'    => 50000,
            'potongan_lain'        => 25000,
            'rekening_bank'        => 'BSI',
            'nomor_rekening'       => '7199998888',
            'atas_nama_rekening'   => 'Ahmad Fauzi',
        ]);

        $response->assertRedirect(route('superadmin.payroll.setting.index'));
        // Untuk guru, gaji_pokok selalu 0 (guru berbasis honor per jam, bukan gaji pokok)
        $this->assertDatabaseHas('payroll_settings', [
            'user_id'        => $this->guru->id,
            'gaji_pokok'     => 0,          // guru tidak punya gaji pokok
            'honor_per_jam'  => 40000,      // guru menggunakan honor per jam
            'nomor_rekening' => '7199998888',
        ]);
    }


    public function test_superadmin_can_create_periode_and_auto_generate_payrolls()
    {
        // 1. Buat Periode
        $response = $this->actingAs($this->superadmin)->post(route('superadmin.payroll.periode.store'), [
            'bulan'              => 11,
            'tahun'              => 2026,
            'nama_periode'       => 'November 2026 Testing',
            'tanggal_mulai'      => '2026-11-01',
            'tanggal_selesai'    => '2026-11-30',
            'tanggal_pembayaran' => '2026-11-25',
        ]);

        $periode = PayrollPeriode::where('bulan', 11)->where('tahun', 2026)->first();
        $this->assertNotNull($periode);

        // 2. Generate Penggajian Massal
        $genResponse = $this->actingAs($this->superadmin)->post(route('superadmin.payroll.periode.generate', $periode));
        $genResponse->assertRedirect(route('superadmin.payroll.periode.show', $periode));

        // Verifikasi slip guru terbentuk
        $guruSlip = Payroll::where('payroll_periode_id', $periode->id)
            ->where('user_id', $this->guru->id)
            ->first();
        $this->assertNotNull($guruSlip);
        $this->assertGreaterThan(0, $guruSlip->gaji_bersih);
        $this->assertGreaterThan(0, $guruSlip->items()->count());

        // Verifikasi slip tendik terbentuk
        $tendikSlip = Payroll::where('payroll_periode_id', $periode->id)
            ->where('user_id', $this->tendik->id)
            ->first();
        $this->assertNotNull($tendikSlip);
        $this->assertGreaterThan(0, $tendikSlip->gaji_bersih);

        // 3. Finalisasi Periode
        $this->actingAs($this->superadmin)->post(route('superadmin.payroll.periode.finalize', $periode));
        $this->assertEquals('finalized', $periode->fresh()->status);

        // 4. Tandai Telah Dibayar
        $this->actingAs($this->superadmin)->post(route('superadmin.payroll.periode.mark-paid', $periode), [
            'tanggal_pembayaran' => '2026-11-25',
        ]);
        $this->assertEquals('paid', $periode->fresh()->status);
        $this->assertEquals('paid', $guruSlip->fresh()->status);

        // Cleanup
        $periode->delete();
    }

    public function test_guru_can_view_own_payroll_and_print_slip()
    {
        // Buat data periode & slip untuk guru
        $periode = PayrollPeriode::firstOrCreate(
            ['bulan' => 12, 'tahun' => 2026],
            [
                'nama_periode'       => 'Desember 2026',
                'status'             => 'paid',
                'tanggal_pembayaran' => '2026-12-25',
            ]
        );

        $payroll = Payroll::firstOrCreate(
            ['payroll_periode_id' => $periode->id, 'user_id' => $this->guru->id],
            [
                'nomor_slip'          => 'SLIP/2026/12/9991',
                'gaji_pokok'          => 1500000,
                'total_penerimaan'    => 2000000,
                'total_potongan'      => 100000,
                'gaji_bersih'         => 1900000,
                'status'              => 'paid',
                'metode_pembayaran'   => 'transfer',
            ]
        );

        // 1. Guru akses halaman riwayat slip
        $response = $this->actingAs($this->guru)->get(route('guru.payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee('Lihat');
        $response->assertSee('Cetak');
        $response->assertSee('SLIP/2026/12/9991');
        $response->assertSee('1.900.000');

        // 2. Guru cetak slip sendiri
        $printResponse = $this->actingAs($this->guru)->get(route('guru.payroll.print', $payroll));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('SLIP GAJI & BISYARAH PEGAWAI', false);
        $printResponse->assertSee($this->guru->name);

        // 3. Guru dilarang melihat slip orang lain
        $tendikPayroll = Payroll::firstOrCreate(
            ['payroll_periode_id' => $periode->id, 'user_id' => $this->tendik->id],
            [
                'nomor_slip'          => 'SLIP/2026/12/9992',
                'gaji_pokok'          => 1800000,
                'total_penerimaan'    => 2100000,
                'total_potongan'      => 100000,
                'gaji_bersih'         => 2000000,
                'status'              => 'paid',
            ]
        );

        $forbiddenResponse = $this->actingAs($this->guru)->get(route('guru.payroll.print', $tendikPayroll));
        $forbiddenResponse->assertStatus(403);

        // Cleanup
        $periode->delete();
    }

    public function test_sso_launcher_redirects_hilalpay_correctly_by_role()
    {
        // 1. Superadmin -> /superadmin/payroll
        $resAdmin = $this->actingAs($this->superadmin)->get(route('sso.launch', $this->hilalpay));
        $resAdmin->assertRedirect(route('superadmin.payroll.dashboard'));

        // 2. Guru -> /guru/payroll
        $resGuru = $this->actingAs($this->guru)->get(route('sso.launch', $this->hilalpay));
        $resGuru->assertRedirect(route('guru.payroll.index'));

        // 3. Tendik -> /tendik/payroll
        $resTendik = $this->actingAs($this->tendik)->get(route('sso.launch', $this->hilalpay));
        $resTendik->assertRedirect(route('tendik.payroll.index'));
    }

    public function test_dynamic_attendance_reflects_actual_daily_presence()
    {
        // 1. Guru sebelum absen di Oktober 2026 -> 0 hari
        $this->assertEquals(0, $this->guru->getHariHadirBulan(10, 2026));

        // 2. Guru melakukan presensi masuk di tanggal 2026-10-02
        \App\Models\PresensiHarianGuru::create([
            'guru_user_id' => $this->guru->id,
            'tanggal'      => '2026-10-02',
            'jam_masuk'    => '06:55:00',
            'status_masuk' => 'hadir',
        ]);

        // Setelah absen 1 hari -> tepat 1 hari
        $this->assertEquals(1, $this->guru->getHariHadirBulan(10, 2026));

        // Guru lain belum absen di Oktober 2026 -> tetap 0 hari
        $this->assertEquals(0, $this->tendik->getHariHadirBulan(10, 2026));
    }

    public function test_superadmin_can_access_payroll_report_and_print_slip()
    {
        $periode = PayrollPeriode::create([
            'bulan'              => 10,
            'tahun'              => 2026,
            'nama_periode'       => 'Oktober 2026 Report Test',
            'tanggal_mulai'      => '2026-10-01',
            'tanggal_selesai'    => '2026-10-31',
            'tanggal_pembayaran' => '2026-10-25',
            'status'             => 'draft',
        ]);

        $payroll = Payroll::create([
            'payroll_periode_id'  => $periode->id,
            'user_id'             => $this->guru->id,
            'nomor_slip'          => 'SLIP/REPORT/001',
            'gaji_pokok'          => 0,
            'total_honor_jam'     => 1000000,
            'total_tunjangan'     => 200000,
            'total_penerimaan'    => 1200000,
            'total_potongan'      => 50000,
            'gaji_bersih'         => 1150000,
            'jumlah_jam_mengajar' => 24,
            'jumlah_kehadiran'    => 12,
            'status'              => 'paid',
        ]);

        // Verifikasi accessor periode_id
        $this->assertEquals($periode->id, $payroll->periode_id);

        // Akses halaman laporan
        $response = $this->actingAs($this->superadmin)->get(route('superadmin.payroll.laporan.index'));
        $response->assertStatus(200);
        $response->assertSee('SLIP/REPORT/001');
        $response->assertSee(route('superadmin.payroll.periode.slip', [$payroll->payroll_periode_id, $payroll->id]));

        // Akses cetak slip
        $slipRes = $this->actingAs($this->superadmin)->get(route('superadmin.payroll.periode.slip', [$payroll->payroll_periode_id, $payroll->id]));
        $slipRes->assertStatus(200);
        $slipRes->assertSee($this->guru->name);
    }
}
