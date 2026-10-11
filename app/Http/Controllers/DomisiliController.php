<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Services\WilayahIndonesiaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DomisiliController extends Controller
{
    /**
     * Tampilan Utama Dashboard Sebaran Domisili
     */
    public function index(Request $request)
    {
        // Pastikan hanya role berwenang yang dapat mengakses
        if (!auth()->check() || !auth()->user()->canAccessDomisili()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat peta dan sebaran domisili.');
        }

        $currentUser = auth()->user();
        $isWaliKelas = $currentUser->isWaliKelas();
        $myKelasList = Kelas::where('wali_kelas_id', $currentUser->id)->with('jurusan')->get();
        $myKelasIds  = $myKelasList->pluck('id')->toArray();

        // Cek apakah pimpinan sekolah (bisa melihat seluruh warga/kelas)
        $isPimpinan = $currentUser->isSuperAdmin()
            || $currentUser->isKepalaSekolah()
            || $currentUser->isWakaKurikulum()
            || $currentUser->isWakaKesiswaan()
            || $currentUser->isWakaSarpras()
            || $currentUser->isWakaHubin()
            || $currentUser->isPembinaOsis()
            || $currentUser->isBk()
            || $currentUser->hasAdminRole('akademik')
            || $currentUser->hasAdminRole('bk');

        $isWaliKelasOnly = $isWaliKelas && !$isPimpinan;

        $roleFilter      = $request->get('role', $isWaliKelasOnly ? 'siswa' : 'all');
        $kabupatenFilter = $request->get('kabupaten');
        $kecamatanFilter = $request->get('kecamatan');
        $kelasFilter     = $request->get('kelas_id');
        $search          = $request->get('q');

        // Jika Wali Kelas murni dan belum pilih kelas, pilih kelas pertamanya
        if ($isWaliKelasOnly && empty($kelasFilter) && count($myKelasIds) > 0) {
            $kelasFilter = $myKelasIds[0];
        }

        // Query dasar warga sekolah (hanya user aktif & role yang relevan)
        $query = User::query()
            ->whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true);

        // Jika filter kelas diterapkan
        if (!empty($kelasFilter)) {
            $query->where('kelas_id', $kelasFilter);
        } elseif ($isWaliKelasOnly && count($myKelasIds) > 0) {
            $query->whereIn('kelas_id', $myKelasIds)->where('role', 'siswa');
        }

        if ($roleFilter !== 'all' && in_array($roleFilter, ['guru', 'tendik', 'siswa'])) {
            $query->where('role', $roleFilter);
        }

        if (!empty($kabupatenFilter)) {
            $query->where(function ($q) use ($kabupatenFilter) {
                $q->where('kabupaten', 'LIKE', '%' . $kabupatenFilter . '%');
            });
        }

        if (!empty($kecamatanFilter)) {
            $query->where(function ($q) use ($kecamatanFilter) {
                $q->where('kecamatan', 'LIKE', '%' . $kecamatanFilter . '%');
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%")
                  ->orWhere('desa', 'LIKE', "%{$search}%")
                  ->orWhere('kecamatan', 'LIKE', "%{$search}%")
                  ->orWhere('kabupaten', 'LIKE', "%{$search}%")
                  ->orWhere('nama_ayah', 'LIKE', "%{$search}%")
                  ->orWhere('nama_ibu', 'LIKE', "%{$search}%");
            });
        }

        // Ambil data users dengan pagination
        $users = (clone $query)
            ->with('kelas')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Statistik
        $scopeQuery = clone $query;
        $totalWarga = (clone $scopeQuery)->count();
        $totalGuru   = (clone $scopeQuery)->where('role', 'guru')->count();
        $totalTendik = (clone $scopeQuery)->where('role', 'tendik')->count();
        $totalSiswa  = (clone $scopeQuery)->where('role', 'siswa')->count();

        $totalTerisi = (clone $scopeQuery)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0')
            ->count();

        // Agregasi Bersih & Normalisasi Kabupaten & Kecamatan
        $rawRecords = (clone $scopeQuery)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0')
            ->select('kabupaten', 'kecamatan')
            ->get();

        $kabCounts = [];
        $kecCounts = [];

        foreach ($rawRecords as $rec) {
            $normKab = WilayahIndonesiaService::normalizeKabupatenName($rec->kabupaten);
            if (!empty($normKab)) {
                $kabCounts[$normKab] = ($kabCounts[$normKab] ?? 0) + 1;
            }

            if (!empty($rec->kecamatan) && $rec->kecamatan !== '0') {
                $cleanKec = trim(preg_replace('/^(kec\.|kecamatan)\s+/i', '', $rec->kecamatan));
                $cleanKec = ucwords(strtolower($cleanKec));
                if (!empty($cleanKec)) {
                    $kecCounts[$cleanKec] = ($kecCounts[$cleanKec] ?? 0) + 1;
                }
            }
        }

        arsort($kabCounts);
        arsort($kecCounts);

        $topKabupaten = collect($kabCounts)->slice(0, 8)->map(fn($total, $kab) => (object)[
            'kabupaten' => $kab,
            'total'     => $total
        ])->values();

        $topKecamatan = collect($kecCounts)->slice(0, 8)->map(fn($total, $kec) => (object)[
            'kecamatan' => $kec,
            'total'     => $total
        ])->values();

        $listKabupaten = array_keys($kabCounts);
        sort($listKabupaten);

        // Daftar Rombel Kelas untuk Filter
        $allKelasList = Kelas::with('jurusan')->get()->sortBy('nama_lengkap');
        $selectedKelas = !empty($kelasFilter) ? Kelas::with('jurusan')->find($kelasFilter) : null;

        return view('domisili.index', compact(
            'users',
            'totalGuru',
            'totalTendik',
            'totalSiswa',
            'totalWarga',
            'totalTerisi',
            'topKabupaten',
            'topKecamatan',
            'listKabupaten',
            'allKelasList',
            'myKelasList',
            'isWaliKelas',
            'isWaliKelasOnly',
            'isPimpinan',
            'selectedKelas',
            'kelasFilter',
            'roleFilter',
            'kabupatenFilter',
            'kecamatanFilter',
            'search'
        ));
    }

    /**
     * Endpoint API Data Sebaran Geografis untuk Peta Leaflet
     */
    public function geoData(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessDomisili()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentUser = auth()->user();
        $isWaliKelas = $currentUser->isWaliKelas();
        $myKelasIds  = Kelas::where('wali_kelas_id', $currentUser->id)->pluck('id')->toArray();

        $isPimpinan = $currentUser->isSuperAdmin()
            || $currentUser->isKepalaSekolah()
            || $currentUser->isWakaKurikulum()
            || $currentUser->isWakaKesiswaan()
            || $currentUser->isWakaSarpras()
            || $currentUser->isWakaHubin()
            || $currentUser->isPembinaOsis()
            || $currentUser->isBk()
            || $currentUser->hasAdminRole('akademik')
            || $currentUser->hasAdminRole('bk');

        $isWaliKelasOnly = $isWaliKelas && !$isPimpinan;

        $roleFilter  = $request->get('role', $isWaliKelasOnly ? 'siswa' : 'all');
        $kelasFilter = $request->get('kelas_id');

        if ($isWaliKelasOnly && empty($kelasFilter) && count($myKelasIds) > 0) {
            $kelasFilter = $myKelasIds[0];
        }

        $query = User::query()
            ->whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0');

        if (!empty($kelasFilter)) {
            $query->where('kelas_id', $kelasFilter);
        } elseif ($isWaliKelasOnly && count($myKelasIds) > 0) {
            $query->whereIn('kelas_id', $myKelasIds)->where('role', 'siswa');
        }

        if ($roleFilter !== 'all' && in_array($roleFilter, ['guru', 'tendik', 'siswa'])) {
            $query->where('role', $roleFilter);
        }

        $allUsers = $query->select(
            'id', 'name', 'role', 'nip', 'no_hp', 'no_hp_ortu', 'nama_ayah', 'nama_ibu', 
            'alamat', 'desa', 'kecamatan', 'kabupaten', 'provinsi', 'kelas_id'
        )->with('kelas')->get();

        // Kelompokkan per wilayah (Kecamatan + Kabupaten atau Desa jika ada)
        $clusters = [];

        foreach ($allUsers as $user) {
            $rawKab = $user->kabupaten;
            $rawKec = $user->kecamatan;
            $rawDes = $user->desa;

            $normKab = WilayahIndonesiaService::normalizeKabupatenName($rawKab);
            $cleanKec = $rawKec ? trim(preg_replace('/^(kec\.|kecamatan)\s+/i', '', $rawKec)) : '';
            $cleanDes = $rawDes ? trim(preg_replace('/^(desa|kel\.|kelurahan)\s+/i', '', $rawDes)) : '';

            $groupKey = ($cleanDes ? "Desa {$cleanDes}, " : '') . ($cleanKec ? "Kec. {$cleanKec}, " : '') . $normKab;

            if (!isset($clusters[$groupKey])) {
                $coords = WilayahIndonesiaService::getCoordinates($normKab, $cleanKec ?: null, $cleanDes ?: null);
                
                $clusters[$groupKey] = [
                    'label'      => $groupKey,
                    'desa'       => $cleanDes,
                    'kecamatan'  => $cleanKec,
                    'kabupaten'  => $normKab,
                    'lat'        => $coords['lat'],
                    'lng'        => $coords['lng'],
                    'total'      => 0,
                    'guru'       => 0,
                    'tendik'     => 0,
                    'siswa'      => 0,
                    'sample'     => [],
                ];
            }

            $clusters[$groupKey]['total']++;
            if ($user->role === 'guru') {
                $clusters[$groupKey]['guru']++;
            } elseif ($user->role === 'tendik') {
                $clusters[$groupKey]['tendik']++;
            } else {
                $clusters[$groupKey]['siswa']++;
            }

            if (count($clusters[$groupKey]['sample']) < 8) {
                $clusters[$groupKey]['sample'][] = [
                    'name'        => $user->name,
                    'role'        => $user->role,
                    'kelas'       => $user->kelas->nama_kelas ?? $user->kelas->nama ?? '',
                    'alamat'      => $user->alamat,
                    'no_hp'       => $user->no_hp,
                    'no_hp_ortu'  => $user->no_hp_ortu,
                    'nama_ayah'   => $user->nama_ayah,
                    'nama_ibu'    => $user->nama_ibu,
                ];
            }
        }

        return response()->json([
            'clusters' => array_values($clusters),
            'total_mapped' => $allUsers->count(),
        ]);
    }
}
