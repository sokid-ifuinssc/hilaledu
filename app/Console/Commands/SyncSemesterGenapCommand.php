<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JadwalPelajaran;
use App\Models\Kurikulum;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Facades\DB;

class SyncSemesterGenapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hilal:sync-genap {--ta= : Tahun Ajaran, default active}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data jadwal pelajaran dan kurikulum mengajar dari Semester Ganjil ke Semester Genap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ta = $this->option('ta') ?: PengaturanSekolah::getActiveTahunAjaran();
        $this->info("Memulai sinkronisasi data Semester Genap untuk Tahun Ajaran: {$ta}...");

        // 1. Sinkronisasi Jadwal Pelajaran
        $ganjilJadwals = JadwalPelajaran::where('tahun_ajaran', $ta)
            ->where(function ($q) {
                $q->where('semester', 'ganjil')
                  ->orWhere('semester', '1')
                  ->orWhere('semester', 'Ganjil');
            })
            ->get();

        $jadwalSynced = 0;
        foreach ($ganjilJadwals as $j) {
            $exists = JadwalPelajaran::where('tahun_ajaran', $ta)
                ->where(function ($q) {
                    $q->where('semester', 'genap')
                      ->orWhere('semester', '2')
                      ->orWhere('semester', 'Genap');
                })
                ->where('hari', $j->hari)
                ->where('jam_ke_mulai', $j->jam_ke_mulai)
                ->where('kelas', $j->kelas)
                ->where('mata_pelajaran_id', $j->mata_pelajaran_id)
                ->where('guru_user_id', $j->guru_user_id)
                ->first();

            if (!$exists) {
                JadwalPelajaran::create([
                    'hari'              => $j->hari,
                    'jam_ke_mulai'      => $j->jam_ke_mulai,
                    'jam_ke_selesai'    => $j->jam_ke_selesai,
                    'jam_mulai'         => $j->jam_mulai,
                    'jam_selesai'       => $j->jam_selesai,
                    'kelas'             => $j->kelas,
                    'mata_pelajaran_id' => $j->mata_pelajaran_id,
                    'guru_user_id'      => $j->guru_user_id,
                    'ruang'             => $j->ruang,
                    'tahun_ajaran'      => $ta,
                    'semester'          => 'genap',
                    'is_locked'         => $j->is_locked,
                ]);
                $jadwalSynced++;
            }
        }
        $this->info("✓ Jadwal Pelajaran disinkronkan ke Semester Genap: {$jadwalSynced} record baru.");

        // 2. Sinkronisasi Kurikulum Mengajar Guru
        $ganjilKurikulums = Kurikulum::where('tahun_ajaran', $ta)
            ->where(function ($q) {
                $q->where('semester', 'Ganjil')
                  ->orWhere('semester', 'ganjil')
                  ->orWhere('semester', '1');
            })
            ->get();

        $kurikulumSynced = 0;
        foreach ($ganjilKurikulums as $k) {
            $exists = Kurikulum::where('tahun_ajaran', $ta)
                ->where(function ($q) {
                    $q->where('semester', 'Genap')
                      ->orWhere('semester', 'genap')
                      ->orWhere('semester', '2');
                })
                ->where('kelas', $k->kelas)
                ->where('mata_pelajaran_id', $k->mata_pelajaran_id)
                ->where('guru_user_id', $k->guru_user_id)
                ->first();

            if (!$exists) {
                Kurikulum::create([
                    'tahun_ajaran'      => $ta,
                    'semester'          => 'Genap',
                    'jenjang'           => $k->jenjang,
                    'jurusan'           => $k->jurusan,
                    'jurusan_id'        => $k->jurusan_id,
                    'kelas'             => $k->kelas,
                    'kelas_id'          => $k->kelas_id,
                    'mata_pelajaran_id' => $k->mata_pelajaran_id,
                    'kategori'          => $k->kategori,
                    'sub_kategori'      => $k->sub_kategori,
                    'urutan'            => $k->urutan,
                    'guru_user_id'      => $k->guru_user_id,
                    'alokasi_jam'       => $k->alokasi_jam,
                    'keterangan'        => $k->keterangan,
                    'kode'              => $k->kode,
                    'nama'              => $k->nama,
                    'tahun_mulai'       => $k->tahun_mulai,
                    'is_aktif'          => $k->is_aktif,
                    'deskripsi'         => $k->deskripsi,
                ]);
                $kurikulumSynced++;
            }
        }
        $this->info("✓ Kurikulum Penugasan Mengajar disinkronkan ke Semester Genap: {$kurikulumSynced} record baru.");

        $this->info("Sinkronisasi Semester Genap selesai dengan sukses!");
        return Command::SUCCESS;
    }
}
