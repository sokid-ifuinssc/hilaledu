<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KalenderAkademikEvent extends Model
{
    protected $table = 'kalender_akademik_events';

    protected $fillable = [
        'kalender_akademik_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'semester',
        'judul_kegiatan',
        'kategori',
        'warna_bg',
        'keterangan',
        'is_libur',
        'sumber',
        'google_event_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date:Y-m-d',
            'tanggal_selesai' => 'date:Y-m-d',
            'is_libur'        => 'boolean',
        ];
    }

    public function kalenderAkademik(): BelongsTo
    {
        return $this->belongsTo(KalenderAkademik::class, 'kalender_akademik_id');
    }

    /**
     * Format rentang tanggal ramah manusia (Contoh: "15 - 20 Juli 2026" atau "17 Agustus 2026")
     */
    public function getFormattedTanggalAttribute(): string
    {
        $start = Carbon::parse($this->tanggal_mulai);
        $end = Carbon::parse($this->tanggal_selesai);

        if ($start->isSameDay($end)) {
            return $start->isoFormat('D MMMM Y');
        }

        if ($start->isSameMonth($end) && $start->isSameYear($end)) {
            return $start->format('j') . ' - ' . $end->isoFormat('D MMMM Y');
        }

        return $start->isoFormat('D MMM') . ' - ' . $end->isoFormat('D MMMM Y');
    }

    /**
     * Dapatkan kelas warna badge Tailwind berdasarkan tipe/warna_bg
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->warna_bg) {
            'red'     => 'bg-rose-500 text-white',
            'yellow'  => 'bg-amber-400 text-slate-900',
            'green'   => 'bg-emerald-500 text-white',
            'blue'    => 'bg-blue-600 text-white',
            'purple'  => 'bg-purple-600 text-white',
            'emerald' => 'bg-emerald-700 text-white',
            'cyan'    => 'bg-cyan-600 text-white',
            default   => 'bg-rose-500 text-white',
        };
    }

    /**
     * Dapatkan warna background untuk cell kalender mini
     */
    public function getCellColorAttribute(): string
    {
        return match ($this->warna_bg) {
            'red'     => 'bg-rose-600 text-white font-black',
            'yellow'  => 'bg-amber-300 text-slate-900 font-black',
            'green'   => 'bg-emerald-200 text-emerald-950 font-bold',
            'blue'    => 'bg-blue-200 text-blue-950 font-bold',
            'purple'  => 'bg-purple-200 text-purple-950 font-bold',
            'cyan'    => 'bg-cyan-200 text-cyan-950 font-bold',
            default   => 'bg-rose-600 text-white font-black',
        };
    }

    /**
     * Dapatkan label kategori yang ramah dibaca
     */
    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'libur_nasional'      => 'Libur Nasional',
            'libur_sekolah'       => 'Libur Sekolah',
            'libur_semester'      => 'Libur Semester',
            'kegiatan_sekolah'    => 'Kegiatan Sekolah',
            'ujian_asesmen'       => 'Asesmen / Ujian',
            'pembagian_rapor'     => 'Pembagian Rapor',
            'hari_efektif_khusus' => 'KBM Efektif Khusus',
            default               => 'Kegiatan',
        };
    }

    public function kegiatanSekolah(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(KegiatanSekolah::class, 'kalender_akademik_event_id');
    }
}
