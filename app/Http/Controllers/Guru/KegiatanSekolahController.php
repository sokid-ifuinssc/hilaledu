<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KegiatanSekolah;
use App\Models\KegiatanPresensi;
use App\Models\KalenderAkademik;
use App\Models\KalenderAkademikEvent;

class KegiatanSekolahController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $bidangFilter = $request->query('bidang');

        $query = KegiatanSekolah::with(['author', 'editor', 'kalenderEvent'])
            ->where('is_active', true)
            ->orderBy('tanggal_kegiatan', 'desc');

        if ($bidangFilter) {
            $query->where('bidang', $bidangFilter);
        }

        $kegiatans = $query->paginate(15);
        $canCreate = $user->canPostSchoolEvent();

        return view('guru.kegiatan.index', compact('kegiatans', 'canCreate', 'bidangFilter'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user->canPostSchoolEvent()) {
            abort(403, 'Anda tidak memiliki hak akses untuk memposting kegiatan sekolah.');
        }

        // Tentukan bidang default berdasarkan query param atau tugas tambahan user
        $reqBidang = $request->query('bidang');
        $defaultBidang = $reqBidang ?: 'waka_kurikulum';
        if (!$reqBidang) {
            if ($user->isWakaHubin()) $defaultBidang = 'waka_hubin';
            elseif ($user->isWakaKesiswaan()) $defaultBidang = 'waka_kesiswaan';
            elseif ($user->isPembinaOsis()) $defaultBidang = 'pembina_osis';
            elseif ($user->isWakaSarpras()) $defaultBidang = 'waka_sarpras';
        }

        // Ambil Kalender Akademik Aktif
        $kalenderAktif = KalenderAkademik::where('is_aktif', true)->first() ?: KalenderAkademik::first();

        return view('guru.kegiatan.create', compact('defaultBidang', 'kalenderAktif'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->canPostSchoolEvent()) {
            abort(403);
        }

        $request->validate([
            'bidang'           => 'required|in:waka_kurikulum,waka_sarpras,waka_kesiswaan,waka_hubin,pembina_osis',
            'judul'            => 'required|string|max:255',
            'kategori'         => 'required|string|max:100',
            'tanggal_kegiatan' => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_kegiatan',
            'waktu_mulai'      => 'nullable',
            'waktu_selesai'    => 'nullable',
            'tempat'           => 'required|string|max:150',
            'sasaran'          => 'required|string|max:100',
            'deskripsi'        => 'required|string',
            'sinkron_kalender' => 'nullable',
            'lampiran'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('kegiatan_lampiran', 'public');
        }

        $tglMulai = $request->tanggal_kegiatan;
        $tglSelesai = $request->tanggal_selesai ?: $tglMulai;

        $kegiatan = KegiatanSekolah::create([
            'author_user_id'   => $user->id,
            'bidang'           => $request->bidang,
            'judul'            => $request->judul,
            'kategori'         => $request->kategori,
            'tanggal_kegiatan' => $tglMulai,
            'tanggal_selesai'  => $tglSelesai,
            'waktu_mulai'      => $request->waktu_mulai,
            'waktu_selesai'    => $request->waktu_selesai,
            'tempat'           => $request->tempat,
            'sasaran'          => $request->sasaran,
            'deskripsi'        => $request->deskripsi,
            'lampiran'         => $lampiranPath,
            'is_active'        => true,
        ]);

        // Sinkronisasi otomatis ke Kalender Akademik Sekolah
        $sinkronKalender = $request->has('sinkron_kalender');
        if ($sinkronKalender) {
            $kalender = KalenderAkademik::where('is_aktif', true)->first() ?: KalenderAkademik::first();
            if ($kalender) {
                $m = (int)date('n', strtotime($tglMulai));
                $semester = ($m >= 7 && $m <= 12) ? '1' : '2';

                $bidangLabels = [
                    'waka_hubin'     => 'Waka Hubin',
                    'waka_kesiswaan' => 'Waka Kesiswaan',
                    'pembina_osis'   => 'Pembina OSIS',
                    'waka_kurikulum' => 'Waka Kurikulum',
                    'waka_sarpras'   => 'Waka Sarpras',
                ];
                $bidangLabel = $bidangLabels[$request->bidang] ?? 'Kegiatan Sekolah';

                $warnaBg = match ($request->bidang) {
                    'waka_hubin'     => 'purple',
                    'waka_kesiswaan' => 'emerald',
                    'pembina_osis'   => 'emerald',
                    'waka_kurikulum' => 'blue',
                    'waka_sarpras'   => 'yellow',
                    default          => 'yellow',
                };

                $event = KalenderAkademikEvent::create([
                    'kalender_akademik_id' => $kalender->id,
                    'tanggal_mulai'        => $tglMulai,
                    'tanggal_selesai'      => $tglSelesai,
                    'semester'             => (string)$semester,
                    'judul_kegiatan'       => "[{$bidangLabel}] " . $request->judul,
                    'kategori'             => 'kegiatan_sekolah',
                    'warna_bg'             => $warnaBg,
                    'keterangan'           => "Tempat: {$request->tempat} | Sasaran: {$request->sasaran} | Penanggung Jawab: {$user->name} ({$bidangLabel}). {$request->deskripsi}",
                    'is_libur'             => false,
                    'sumber'               => 'tugas_tambahan',
                ]);

                $kegiatan->kalender_akademik_event_id = $event->id;
                $kegiatan->save();
            }
        }

        $msg = "Agenda kegiatan sekolah \"{$request->judul}\" berhasil diposting" . ($kegiatan->kalender_akademik_event_id ? " dan otomatis disinkronkan ke Kalender Akademik Sekolah!" : "!");
        return redirect()->route('guru.kegiatan.index')->with('success', $msg);
    }

    public function show(KegiatanSekolah $kegiatan)
    {
        $kegiatan->load(['author', 'editor', 'presensis.guru', 'kalenderEvent']);
        $presensiSaya = $kegiatan->presensis->firstWhere('guru_user_id', Auth::id());
        return view('guru.kegiatan.show', compact('kegiatan', 'presensiSaya'));
    }

    public function edit(KegiatanSekolah $kegiatan)
    {
        $user = Auth::user();
        if (!$kegiatan->canBeEditedBy($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit kegiatan ini.');
        }

        $kalenderAktif = KalenderAkademik::where('is_aktif', true)->first() ?: KalenderAkademik::first();

        return view('guru.kegiatan.edit', compact('kegiatan', 'kalenderAktif'));
    }

    public function update(Request $request, KegiatanSekolah $kegiatan)
    {
        $user = Auth::user();
        if (!$kegiatan->canBeEditedBy($user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit kegiatan ini.');
        }

        $request->validate([
            'bidang'           => 'required|in:waka_kurikulum,waka_sarpras,waka_kesiswaan,waka_hubin,pembina_osis',
            'judul'            => 'required|string|max:255',
            'kategori'         => 'required|string|max:100',
            'tanggal_kegiatan' => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_kegiatan',
            'waktu_mulai'      => 'nullable',
            'waktu_selesai'    => 'nullable',
            'tempat'           => 'required|string|max:150',
            'sasaran'          => 'required|string|max:100',
            'deskripsi'        => 'required|string',
            'sinkron_kalender' => 'nullable',
            'lampiran'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
        ]);

        $tglMulai = $request->tanggal_kegiatan;
        $tglSelesai = $request->tanggal_selesai ?: $tglMulai;

        $data = [
            'bidang'            => $request->bidang,
            'judul'             => $request->judul,
            'kategori'          => $request->kategori,
            'tanggal_kegiatan'  => $tglMulai,
            'tanggal_selesai'   => $tglSelesai,
            'waktu_mulai'       => $request->waktu_mulai,
            'waktu_selesai'     => $request->waktu_selesai,
            'tempat'            => $request->tempat,
            'sasaran'           => $request->sasaran,
            'deskripsi'         => $request->deskripsi,
            'edited_by_user_id' => $user->id,
        ];

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('kegiatan_lampiran', 'public');
        }

        $bidangLabels = [
            'waka_hubin'     => 'Waka Hubin',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'pembina_osis'   => 'Pembina OSIS',
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_sarpras'   => 'Waka Sarpras',
        ];
        $bidangLabel = $bidangLabels[$request->bidang] ?? 'Kegiatan Sekolah';

        $warnaBg = match ($request->bidang) {
            'waka_hubin'     => 'purple',
            'waka_kesiswaan' => 'emerald',
            'pembina_osis'   => 'emerald',
            'waka_kurikulum' => 'blue',
            'waka_sarpras'   => 'yellow',
            default          => 'yellow',
        };

        $m = (int)date('n', strtotime($tglMulai));
        $semester = ($m >= 7 && $m <= 12) ? '1' : '2';

        // Update atau sinkronkan ke Kalender Akademik
        if ($kegiatan->kalender_akademik_event_id) {
            $event = KalenderAkademikEvent::find($kegiatan->kalender_akademik_event_id);
            if ($event) {
                $event->update([
                    'tanggal_mulai'   => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'semester'        => (string)$semester,
                    'judul_kegiatan'  => "[{$bidangLabel}] " . $request->judul,
                    'warna_bg'        => $warnaBg,
                    'keterangan'      => "Tempat: {$request->tempat} | Sasaran: {$request->sasaran} | Penanggung Jawab: {$user->name} ({$bidangLabel}). {$request->deskripsi}",
                ]);
            }
        } elseif ($request->has('sinkron_kalender')) {
            $kalender = KalenderAkademik::where('is_aktif', true)->first() ?: KalenderAkademik::first();
            if ($kalender) {
                $event = KalenderAkademikEvent::create([
                    'kalender_akademik_id' => $kalender->id,
                    'tanggal_mulai'        => $tglMulai,
                    'tanggal_selesai'      => $tglSelesai,
                    'semester'             => (string)$semester,
                    'judul_kegiatan'       => "[{$bidangLabel}] " . $request->judul,
                    'kategori'             => 'kegiatan_sekolah',
                    'warna_bg'             => $warnaBg,
                    'keterangan'           => "Tempat: {$request->tempat} | Sasaran: {$request->sasaran} | Penanggung Jawab: {$user->name} ({$bidangLabel}). {$request->deskripsi}",
                    'is_libur'             => false,
                    'sumber'               => 'tugas_tambahan',
                ]);
                $data['kalender_akademik_event_id'] = $event->id;
            }
        }

        $kegiatan->update($data);

        return redirect()->route('guru.kegiatan.show', $kegiatan)
            ->with('success', 'Agenda kegiatan sekolah berhasil diperbarui dan disinkronkan ke Kalender Akademik!');
    }

    public function storePresensi(Request $request, KegiatanSekolah $kegiatan)
    {
        $user = Auth::user();

        $request->validate([
            'status'     => 'required|in:hadir,terlambat,izin,sakit',
            'catatan'    => 'nullable|string|max:500',
            'foto_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('kegiatan_presensi', 'public');
        }

        $data = [
            'status'         => $request->status,
            'waktu_presensi' => now(),
            'catatan'        => $request->catatan,
        ];
        if ($fotoPath) {
            $data['foto_bukti'] = $fotoPath;
        }

        $presensi = KegiatanPresensi::updateOrCreate(
            [
                'kegiatan_sekolah_id' => $kegiatan->id,
                'guru_user_id'        => $user->id,
            ],
            $data
        );

        return back()->with('success', 'Kehadiran kegiatan "' . $kegiatan->judul . '" berhasil dicatat: Status ' . $presensi->status_label . '.');
    }

    public function destroy(KegiatanSekolah $kegiatan)
    {
        $user = Auth::user();
        if (!$kegiatan->canBeEditedBy($user)) {
            abort(403);
        }

        // Hapus juga event di Kalender Akademik jika tersinkron
        if ($kegiatan->kalender_akademik_event_id) {
            KalenderAkademikEvent::where('id', $kegiatan->kalender_akademik_event_id)->delete();
        }

        $kegiatan->delete();
        return redirect()->route('guru.kegiatan.index')->with('success', 'Kegiatan sekolah berhasil dihapus dari daftar dan kalender akademik.');
    }
}
