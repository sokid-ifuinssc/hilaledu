<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PresensiHarianSiswa;
use App\Models\Kelas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PresensiHarianController extends Controller
{
    /**
     * Menampilkan halaman monitoring presensi harian siswa.
     */
        public function index(Request $request)
    {
        $user = Auth::user();
        
        // Scope variables
        $kelasQuery = Kelas::where('is_aktif', true);
        $canEdit = false;
        $canSetKetuaKelas = false;
        
        // Authorization Logic
        if ($user->isSuperAdmin() || $user->isKepalaSekolah() || $user->isWakaKesiswaan() || $user->isWakaKurikulum() || $user->isBk() || $user->isPembinaOsis() || $user->isPetugasPiket()) {
            if ($user->isPetugasPiket() || $user->isSuperAdmin()) {
                $canEdit = true;
            }
        } elseif ($user->isKaprog()) {
            $jurusanIds = \App\Models\Jurusan::where('kaprog_id', $user->id)->pluck('id');
            $kelasQuery->whereIn('jurusan_id', $jurusanIds);
        } elseif ($user->isWaliKelas()) {
            $kelasQuery->where('wali_kelas_id', $user->id);
            $canSetKetuaKelas = true;
        } elseif ($user->isSiswa()) {
            $kelas = Kelas::find($user->kelas_id);
            if ($kelas && $kelas->ketua_kelas_id == $user->id) {
                $kelasQuery->where('id', $user->kelas_id);
                $canEdit = true;
            } else {
                return redirect()->route('siswa.dashboard')->with('error', 'Akses ditolak.');
            }
        } else {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $kelasList = $kelasQuery->get();
        $mapelList = \App\Models\MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();
        
        $jenisPresensi = $request->input('jenis_presensi', 'harian');
        $periodeWaktu = $request->input('periode_waktu', 'hari');
        $filterTanggal = $request->input('tanggal', Carbon::today()->format('Y-m-d'));
        $filterBulan = $request->input('bulan');
        $filterTahun = $request->input('tahun', date('Y'));
        $filterKelas = $request->input('kelas_id');
        $filterMapel = $request->input('mapel_id');
        
        // Allowed kelas_id for this user
        $allowedKelasIds = $kelasList->pluck('id');
        if ($filterKelas && $allowedKelasIds->contains($filterKelas)) {
            $activeKelasIds = collect([$filterKelas]);
        } else {
            $activeKelasIds = $allowedKelasIds;
        }

        // Data for Monitoring Table
        $presensiData = collect();
        
        if ($jenisPresensi === 'mapel') {
            $presensiQuery = \App\Models\LaporanKbmPresensi::with(['siswa', 'laporan.jadwal.mataPelajaran'])
                ->join('laporan_kbms', 'laporan_kbm_presensis.laporan_kbm_id', '=', 'laporan_kbms.id')
                ->join('jadwal_pelajarans', 'laporan_kbms.jadwal_pelajaran_id', '=', 'jadwal_pelajarans.id')
                ->select('laporan_kbm_presensis.*', 'laporan_kbms.tanggal_realisasi as tanggal', 'jadwal_pelajarans.kelas_id', 'jadwal_pelajarans.mata_pelajaran_id');
                
            if ($periodeWaktu === 'bulan' && $filterBulan) {
                $presensiQuery->whereMonth('laporan_kbms.tanggal_realisasi', date('m', strtotime($filterBulan)))
                              ->whereYear('laporan_kbms.tanggal_realisasi', $filterTahun);
            } elseif ($periodeWaktu === 'semester') {
                $activeTa = \App\Models\TahunAjaran::where('is_aktif', true)->orWhere('is_active', true)->first();
                if ($activeTa) {
                    $presensiQuery->whereYear('laporan_kbms.tanggal_realisasi', '>=', $activeTa->tahun_mulai)
                                  ->whereYear('laporan_kbms.tanggal_realisasi', '<=', $activeTa->tahun_selesai);
                }
            } else { // hari
                $presensiQuery->whereDate('laporan_kbms.tanggal_realisasi', $filterTanggal);
            }
            
            $presensiQuery->whereIn('jadwal_pelajarans.kelas_id', $activeKelasIds);

            if ($filterMapel) {
                $presensiQuery->where('jadwal_pelajarans.mata_pelajaran_id', $filterMapel);
            }
            
            $rawData = $presensiQuery->orderBy('laporan_kbms.tanggal_realisasi', 'desc')->get();
            
            if ($periodeWaktu === 'hari') {
                // For 'hari' view, Mapel can have multiple subjects a day per student. We group them.
                $presensiData = $rawData->groupBy('siswa_user_id'); 
            } else {
                // Rekap (Bulan/Semester)
                $arr = [];
                foreach ($rawData as $row) {
                    if (!isset($arr[$row->siswa_user_id])) {
                        $arr[$row->siswa_user_id] = ['Hadir'=>0, 'Sakit'=>0, 'Izin'=>0, 'Alpa'=>0, 'Terlambat'=>0];
                    }
                    $status = ucfirst(strtolower($row->status));
                    if (isset($arr[$row->siswa_user_id][$status])) {
                        $arr[$row->siswa_user_id][$status]++;
                    }
                }
                $presensiData = collect($arr);
            }
        } else {
            // Harian
            $presensiQuery = PresensiHarianSiswa::with(['siswa', 'kelas', 'penginput']);
            
            if ($periodeWaktu === 'bulan' && $filterBulan) {
                $presensiQuery->whereMonth('tanggal', date('m', strtotime($filterBulan)))
                              ->whereYear('tanggal', $filterTahun);
            } elseif ($periodeWaktu === 'semester') {
                $activeTa = \App\Models\TahunAjaran::where('is_aktif', true)->orWhere('is_active', true)->first();
                if ($activeTa) {
                    $presensiQuery->whereYear('tanggal', '>=', $activeTa->tahun_mulai)
                                  ->whereYear('tanggal', '<=', $activeTa->tahun_selesai);
                }
            } else {
                $presensiQuery->whereDate('tanggal', $filterTanggal);
            }
            
            $presensiQuery->whereIn('kelas_id', $activeKelasIds);
            
            $rawData = $presensiQuery->orderBy('tanggal', 'desc')->get();
            
            if ($periodeWaktu === 'hari') {
                $presensiData = $rawData->keyBy('siswa_id'); // 1 per day
            } else {
                // Rekap
                $arr = [];
                foreach ($rawData as $row) {
                    if (!isset($arr[$row->siswa_id])) {
                        $arr[$row->siswa_id] = ['Hadir'=>0, 'Sakit'=>0, 'Izin'=>0, 'Alpa'=>0, 'Terlambat'=>0];
                    }
                    $status = ucfirst(strtolower($row->status));
                    if (isset($arr[$row->siswa_id][$status])) {
                        $arr[$row->siswa_id][$status]++;
                    }
                }
                $presensiData = collect($arr);
            }
        }

        // Data for Input Modal (If can edit and a class is selected)
        $siswasForInput = [];
        $existingPresensi = [];
        $selectedKelas = null;
        if ($canEdit && $filterKelas && $jenisPresensi === 'harian' && $periodeWaktu === 'hari') {
            $selectedKelas = Kelas::with('ketuaKelas')->find($filterKelas);
            if ($selectedKelas && $allowedKelasIds->contains($filterKelas)) {
                $siswasForInput = User::where('role', 'siswa')
                                    ->where('kelas_id', $filterKelas)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
                                    
                $existingPresensi = PresensiHarianSiswa::where('kelas_id', $filterKelas)
                                        ->whereDate('tanggal', $filterTanggal)
                                        ->get()
                                        ->keyBy('siswa_id');
            }
        }
        
        // Get all students to show in the table if a class is selected
        $students = collect();
        if ($filterKelas && $allowedKelasIds->contains($filterKelas)) {
            $students = User::where('role', 'siswa')
                            ->where('kelas_id', $filterKelas)
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get();
        }

        // If Walikelas, they need to see their class to set ketua kelas
        $myKelas = null;
        $siswaMyKelas = [];
        if ($canSetKetuaKelas) {
            $myKelas = Kelas::where('wali_kelas_id', $user->id)->first();
            if ($myKelas) {
                $siswaMyKelas = User::where('role', 'siswa')
                                    ->where('kelas_id', $myKelas->id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
            }
        }

        return view('presensi-harian.index', compact(
            'kelasList', 'mapelList', 'presensiData', 'canEdit', 'canSetKetuaKelas', 
            'jenisPresensi', 'periodeWaktu', 'filterTanggal', 'filterBulan', 'filterTahun', 'filterKelas', 'filterMapel',
            'siswasForInput', 'existingPresensi', 'selectedKelas',
            'myKelas', 'siswaMyKelas', 'students'
        ));
    }

    /**
     * Siswa absen hadir mandiri.
     */
    public function absenHadir(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSiswa()) {
            return redirect()->back()->with('error', 'Hanya siswa yang dapat melakukan absensi mandiri.');
        }

        if (!$user->kelas_id) {
            return redirect()->back()->with('error', 'Anda belum memiliki kelas.');
        }

        $tanggal = Carbon::today()->format('Y-m-d');
        
        $existing = PresensiHarianSiswa::where('siswa_id', $user->id)
            ->whereDate('tanggal', $tanggal)
            ->first();
            
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah melakukan absensi hari ini.');
        }

        PresensiHarianSiswa::create([
            'siswa_id' => $user->id,
            'kelas_id' => $user->kelas_id,
            'tanggal' => $tanggal,
            'status' => 'Hadir',
            'diinput_oleh' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Berhasil melakukan absensi Hadir.');
    }

    /**
     * Ketua Kelas / Petugas Piket mengabsen satu kelas.
     */
    public function absenKelas(Request $request)
    {
        $user = Auth::user();
        $kelasId = $request->input('kelas_id');
        $tanggal = $request->input('tanggal', Carbon::today()->format('Y-m-d'));
        $absensi = $request->input('absensi'); // array: siswa_id => ['status' => '...', 'keterangan' => '...']

        $kelas = Kelas::findOrFail($kelasId);

        // Validation Access
        $canEdit = false;
        if ($user->isSuperAdmin() || $user->isPetugasPiket()) {
            $canEdit = true;
        } elseif ($user->isSiswa() && $kelas->ketua_kelas_id == $user->id) {
            $canEdit = true;
        }

        if (!$canEdit) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengisi absensi untuk kelas ini.');
        }

        foreach ($absensi as $siswaId => $data) {
            if (!empty($data['status'])) {
                PresensiHarianSiswa::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'status' => $data['status'],
                        'keterangan' => $data['keterangan'] ?? null,
                        'diinput_oleh' => $user->id,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Berhasil menyimpan absensi kelas.');
    }

    /**
     * Wali Kelas mengatur ketua kelas.
     */
    public function setKetuaKelas(Request $request)
    {
        $user = Auth::user();
        $kelasId = $request->input('kelas_id');
        $siswaId = $request->input('siswa_id');

        $kelas = Kelas::findOrFail($kelasId);

        if (!$user->isSuperAdmin() && $kelas->wali_kelas_id != $user->id) {
            return redirect()->back()->with('error', 'Anda bukan wali kelas dari kelas ini.');
        }

        $kelas->update(['ketua_kelas_id' => $siswaId]);

        return redirect()->back()->with('success', 'Berhasil menetapkan ketua kelas.');
    }
}
