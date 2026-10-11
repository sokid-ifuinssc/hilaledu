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

        $roleFilter = $request->get('role', 'all');
        $kabupatenFilter = $request->get('kabupaten');
        $kecamatanFilter = $request->get('kecamatan');
        $search = $request->get('q');

        // Query dasar warga sekolah (hanya user aktif & role yang relevan)
        $query = User::query()
            ->whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true);

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
                  ->orWhere('kabupaten', 'LIKE', "%{$search}%");
            });
        }

        // Ambil data users dengan pagination
        $users = (clone $query)
            ->with('kelas')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Statistik Keseluruhan
        $totalGuru   = User::where('role', 'guru')->where('is_active', true)->count();
        $totalTendik = User::where('role', 'tendik')->where('is_active', true)->count();
        $totalSiswa  = User::where('role', 'siswa')->where('is_active', true)->count();
        $totalWarga  = $totalGuru + $totalTendik + $totalSiswa;

        $totalTerisi = User::whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0')
            ->count();

        // Top 5 Kabupaten
        $topKabupaten = User::whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0')
            ->select('kabupaten', DB::raw('count(*) as total'))
            ->groupBy('kabupaten')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Top 8 Kecamatan
        $topKecamatan = User::whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true)
            ->whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->where('kecamatan', '!=', '0')
            ->select('kecamatan', 'kabupaten', DB::raw('count(*) as total'))
            ->groupBy('kecamatan', 'kabupaten')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Daftar Kabupaten untuk filter
        $listKabupaten = User::whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0')
            ->distinct()
            ->orderBy('kabupaten')
            ->pluck('kabupaten');

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

        $roleFilter = $request->get('role', 'all');

        $query = User::query()
            ->whereIn('role', ['guru', 'tendik', 'siswa'])
            ->where('is_active', true)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->where('kabupaten', '!=', '0');

        if ($roleFilter !== 'all' && in_array($roleFilter, ['guru', 'tendik', 'siswa'])) {
            $query->where('role', $roleFilter);
        }

        $allUsers = $query->select('id', 'name', 'role', 'desa', 'kecamatan', 'kabupaten', 'provinsi')->get();

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

            if (count($clusters[$groupKey]['sample']) < 6) {
                $clusters[$groupKey]['sample'][] = [
                    'name' => $user->name,
                    'role' => $user->role,
                ];
            }
        }

        return response()->json([
            'clusters' => array_values($clusters),
            'total_mapped' => $allUsers->count(),
        ]);
    }
}
