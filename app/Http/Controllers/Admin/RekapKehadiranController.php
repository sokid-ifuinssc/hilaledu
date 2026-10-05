<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSekolah;
use App\Models\User;
use App\Services\RekapKehadiranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Rekap kehadiran pegawai (guru + tendik) dan kehadiran mengajar guru.
 *
 *  - pegawai / mengajar : seluruh pegawai (Kurikulum, Kepala Sekolah, Bendahara/Payroll)
 *  - saya               : rekap pribadi untuk guru / tendik yang login
 */
class RekapKehadiranController extends Controller
{
    public function __construct(protected RekapKehadiranService $service)
    {
    }

    // ------------------------------------------------------------------
    // SELURUH PEGAWAI
    // ------------------------------------------------------------------

    public function pegawai(Request $request)
    {
        $this->authorizeAll();
        $periode = $this->periode($request);
        $rekap = $this->service->rekapPegawai($periode);

        $detail = null;
        if ($id = $request->query('detail')) {
            $u = User::whereIn('role', ['guru', 'tendik'])->find($id);
            if ($u) {
                $detail = $this->service->rekapPegawai($periode, collect([$u]), true)['rows']->first();
            }
        }

        return view('rekap_kehadiran.pegawai', [
            'periode'   => $periode,
            'rekap'     => $rekap,
            'detail'    => $detail,
            'taOptions' => $this->taOptions(),
            'canKbmLog' => $this->canOpenKbmLog(),
        ]);
    }

    public function pegawaiPrint(Request $request)
    {
        $this->authorizeAll();
        $periode = $this->periode($request);

        return view('rekap_kehadiran.print_pegawai', [
            'periode'  => $periode,
            'rekap'    => $this->service->rekapPegawai($periode),
            'settings' => $this->service->signatureSettings(),
            'selfName' => null,
        ]);
    }

    public function mengajar(Request $request)
    {
        $this->authorizeAll();
        $periode = $this->periode($request);
        $rekap = $this->service->rekapMengajar($periode);

        $detail = null;
        if ($id = $request->query('detail')) {
            $u = User::where('role', 'guru')->find($id);
            if ($u) {
                $detail = $this->service->rekapMengajar($periode, collect([$u]), true)['rows']->first();
            }
        }

        return view('rekap_kehadiran.mengajar', [
            'periode'   => $periode,
            'rekap'     => $rekap,
            'detail'    => $detail,
            'taOptions' => $this->taOptions(),
            'canKbmLog' => $this->canOpenKbmLog(),
        ]);
    }

    public function mengajarPrint(Request $request)
    {
        $this->authorizeAll();
        $periode = $this->periode($request);

        return view('rekap_kehadiran.print_mengajar', [
            'periode'  => $periode,
            'rekap'    => $this->service->rekapMengajar($periode),
            'settings' => $this->service->signatureSettings(),
            'selfName' => null,
        ]);
    }

    // ------------------------------------------------------------------
    // REKAP PRIBADI (GURU / TENDIK)
    // ------------------------------------------------------------------

    public function saya(Request $request)
    {
        $user = $this->selfUser();
        $periode = $this->periode($request);
        $isGuru = $user->role === 'guru';

        return view('rekap_kehadiran.saya', [
            'user'      => $user,
            'periode'   => $periode,
            'isGuru'    => $isGuru,
            'pegawai'   => $this->service->rekapPegawai($periode, collect([$user]), true),
            'mengajar'  => $isGuru ? $this->service->rekapMengajar($periode, collect([$user]), true) : null,
            'taOptions' => $this->taOptions(),
        ]);
    }

    public function sayaPrint(Request $request)
    {
        $user = $this->selfUser();
        $periode = $this->periode($request);
        $settings = $this->service->signatureSettings();

        if ($request->query('jenis') === 'mengajar' && $user->role === 'guru') {
            return view('rekap_kehadiran.print_mengajar', [
                'periode'  => $periode,
                'rekap'    => $this->service->rekapMengajar($periode, collect([$user])),
                'settings' => $settings,
                'selfName' => $user->name,
            ]);
        }

        return view('rekap_kehadiran.print_pegawai', [
            'periode'  => $periode,
            'rekap'    => $this->service->rekapPegawai($periode, collect([$user])),
            'settings' => $settings,
            'selfName' => $user->name,
        ]);
    }

    // ------------------------------------------------------------------
    // HELPER
    // ------------------------------------------------------------------

    protected function authorizeAll(): void
    {
        abort_unless(Auth::user()?->canViewRekapKehadiran(), 403, 'Anda tidak memiliki akses ke rekap kehadiran pegawai.');
    }

    protected function selfUser(): User
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['guru', 'tendik'], true), 403, 'Rekap pribadi hanya untuk guru dan tendik.');

        return $user;
    }

    protected function periode(Request $request): array
    {
        return $this->service->resolvePeriode(
            $request->query('mode'),
            $request->query('bulan'),
            $request->query('tahun_ajaran'),
            $request->query('semester')
        );
    }

    /**
     * Detail per hari mengajar sudah ada di menu "Presensi Mengajar Guru" (modul akademik).
     * Tautan hanya ditampilkan bagi yang bisa membukanya.
     */
    protected function canOpenKbmLog(): bool
    {
        $u = Auth::user();

        return $u && ($u->isSuperAdmin() || $u->hasAdminRole('akademik') || in_array($u->role, ['guru', 'tendik'], true));
    }

    protected function taOptions(): array
    {
        $aktif = (string) PengaturanSekolah::get('tahun_pelajaran', '2026/2027');
        $startYear = (int) substr($aktif, 0, 4) ?: (int) date('Y');

        $options = [];
        for ($y = $startYear - 3; $y <= $startYear + 1; $y++) {
            $options[] = $y . '/' . ($y + 1);
        }

        return $options;
    }
}
