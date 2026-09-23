<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Catat audit log untuk perubahan pada model.
     */
    public static function log(
        Model $model,
        string $aksi,
        ?string $statusDari = null,
        ?string $statusKe = null,
        ?string $keterangan = null,
        ?array $dataLama = null,
        ?array $dataBaru = null
    ): AuditLog {
        return AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'status_dari' => $statusDari,
            'status_ke' => $statusKe,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Log pembuatan pelanggaran baru.
     */
    public static function logPelanggaranCreated(Model $pelanggaran, string $jenisTindakan): AuditLog
    {
        return self::log(
            $pelanggaran,
            'created',
            null,
            $pelanggaran->status,
            "Pelanggaran dicatat dengan tindakan: {$jenisTindakan}"
        );
    }

    /**
     * Log perubahan status progres.
     */
    public static function logStatusChanged(Model $progres, string $dari, string $ke, ?string $keterangan = null): AuditLog
    {
        return self::log(
            $progres,
            'status_changed',
            $dari,
            $ke,
            $keterangan
        );
    }

    /**
     * Log approval (setuju/tolak).
     */
    public static function logApproval(Model $progres, string $role, string $keputusan, ?string $catatan = null): AuditLog
    {
        $aksi = $keputusan === 'ditolak' ? 'rejected' : 'approval';
        $label = $keputusan === 'disetujui' ? 'menyetujui' : 'menolak';

        return self::log(
            $progres,
            $aksi,
            null,
            $keputusan,
            "{$role} {$label} progres." . ($catatan ? " Catatan: {$catatan}" : '')
        );
    }

    /**
     * Log pengisian laporan.
     */
    public static function logLaporan(Model $progres, string $role): AuditLog
    {
        return self::log(
            $progres,
            'laporan',
            null,
            null,
            "{$role} mengisi laporan."
        );
    }

    /**
     * Log cetak surat.
     */
    public static function logCetakSurat(Model $progres): AuditLog
    {
        return self::log(
            $progres,
            'cetak_surat',
            null,
            null,
            'Surat berhasil dicetak oleh BK.'
        );
    }

    /**
     * Log kasus ditutup/selesai.
     */
    public static function logClosed(Model $progres): AuditLog
    {
        return self::log(
            $progres,
            'closed',
            $progres->status,
            'selesai',
            'Kasus ditutup/diselesaikan oleh BK.'
        );
    }

    /**
     * Log revisi kasus yang dikembalikan.
     */
    public static function logRevisi(Model $progres, ?string $dariTindakan, ?string $keTindakan, ?string $keterangan = null): AuditLog
    {
        return self::log(
            $progres,
            'revisi',
            $dariTindakan,
            $keTindakan,
            $keterangan ?? "Jenis tindakan diubah dari {$dariTindakan} ke {$keTindakan}"
        );
    }
}
