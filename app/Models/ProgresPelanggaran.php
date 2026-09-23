<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgresPelanggaran extends Model
{
    use HasFactory;


    protected $fillable = [
        'pelanggaran_id',
        'jenis_tindakan',
        'approval_walikelas',
        'approval_walikelas_at',
        'catatan_walikelas',
        'dokumen_walikelas',
        'approval_kaprog',
        'approval_kaprog_at',
        'catatan_kaprog',
        'dokumen_kaprog',
        'approval_waka',
        'approval_waka_at',
        'catatan_waka',
        'dokumen_waka',
        'approval_kepsek',
        'approval_kepsek_at',
        'catatan_kepsek',
        'dokumen_kepsek',
        'surat_dicetak',
        'surat_dicetak_at',
        'laporan_walikelas',
        'dokumen_laporan_walikelas',
        'laporan_kaprog',
        'dokumen_laporan_kaprog',
        'laporan_waka',
        'dokumen_laporan_waka',
        'laporan_kepsek',
        'dokumen_laporan_kepsek',
        'laporan_bk',
        'dokumen_laporan_bk',
        'kesimpulan',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'approval_walikelas_at' => 'datetime',
            'approval_kaprog_at' => 'datetime',
            'approval_waka_at' => 'datetime',
            'approval_kepsek_at' => 'datetime',
            'surat_dicetak' => 'boolean',
            'surat_dicetak_at' => 'datetime',
        ];
    }

    // =====================
    // Relationships
    // =====================

    public function pelanggaran()
    {
        return $this->belongsTo(Pelanggaran::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderBy('created_at', 'desc');
    }

    // =====================
    // Jenis Tindakan Config
    // =====================

    /**
     * Daftar jenis tindakan dengan label
     */
    public static function jenisTindakanOptions(): array
    {
        return [
            'peringatan_lisan' => 'Peringatan Lisan',
            'teguran_lisan' => 'Teguran Lisan',
            'home_visit' => 'Home Visit',
            'pemanggilan_ortu' => 'Pemanggilan Orang Tua',
            'sp1' => 'Surat Peringatan 1 (SP-1)',
            'sp2' => 'Surat Peringatan 2 (SP-2)',
            'sp3' => 'Surat Peringatan 3 (SP-3)',
        ];
    }

    /**
     * Urutan eskalasi jenis tindakan
     */
    public static function urutanEskalasi(): array
    {
        return [
            'peringatan_lisan',
            'teguran_lisan',
            'home_visit',
            'pemanggilan_ortu',
            'sp1',
            'sp2',
            'sp3',
        ];
    }

    /**
     * Mendapatkan jenis tindakan berikutnya berdasarkan histori terakhir
     */
    public static function getNextEskalasi(?string $lastTindakan): string
    {
        $urutan = self::urutanEskalasi();
        if (!$lastTindakan) {
            return $urutan[0]; // peringatan_lisan
        }
        $currentIndex = array_search($lastTindakan, $urutan);
        if ($currentIndex === false || $currentIndex >= count($urutan) - 1) {
            return end($urutan); // sp3
        }
        return $urutan[$currentIndex + 1];
    }

    /**
     * Role yang dibutuhkan untuk approval berdasarkan jenis tindakan
     */
    public function getRequiredApprovalRoles(): array
    {
        return match ($this->jenis_tindakan) {
            'peringatan_lisan' => [],
            'teguran_lisan' => [],
            'home_visit' => [],
            'pemanggilan_ortu' => [],
            'sp1' => ['walikelas', 'kaprog'],
            'sp2' => ['walikelas', 'kaprog', 'waka'],
            'sp3' => ['walikelas', 'kaprog', 'waka', 'kepsek'],
            default => [],
        };
    }

    /**
     * Role yang dibutuhkan untuk mengisi laporan berdasarkan jenis tindakan
     *
     * Sesuai spec:
     * - Teguran Lisan: Wali Kelas
     * - Home Visit: Wali Kelas
     * - Pemanggilan Ortu: Wali Kelas
     * - SP1: Wali Kelas, Kaprog, BK
     * - SP2: Wali Kelas, Kaprog, Waka, BK
     * - SP3: Wali Kelas, Kaprog, Waka, Kepsek (kesimpulan)
     */
    public function getRequiredLaporanRoles(): array
    {
        return match ($this->jenis_tindakan) {
            'peringatan_lisan' => [],
            'teguran_lisan' => ['walikelas'],
            'home_visit' => ['walikelas'],
            'pemanggilan_ortu' => ['walikelas'],
            'sp1' => ['walikelas', 'kaprog', 'bk'],
            'sp2' => ['walikelas', 'kaprog', 'waka', 'bk'],
            'sp3' => ['walikelas', 'kaprog', 'waka', 'kepsek'],
            default => [],
        };
    }

    /**
     * Apakah jenis tindakan ini memerlukan cetak surat
     */
    public function requiresCetakSurat(): bool
    {
        return in_array($this->jenis_tindakan, ['pemanggilan_ortu', 'sp1', 'sp2', 'sp3']);
    }

    // =====================
    // Status Checks
    // =====================

    /**
     * Cek apakah semua approval yang dibutuhkan sudah selesai
     */
    public function isApprovalComplete(): bool
    {
        $roles = $this->getRequiredApprovalRoles();
        if (empty($roles)) return true;

        foreach ($roles as $role) {
            $field = "approval_{$role}";
            if ($this->$field !== 'disetujui') {
                return false;
            }
        }
        return true;
    }

    /**
     * Cek apakah ada approval yang ditolak
     */
    public function hasRejectedApproval(): bool
    {
        $roles = $this->getRequiredApprovalRoles();
        foreach ($roles as $role) {
            $field = "approval_{$role}";
            if ($this->$field === 'ditolak') {
                return true;
            }
        }
        return false;
    }

    /**
     * Dapatkan role yang menolak
     */
    public function getRejectedBy(): ?string
    {
        $roles = $this->getRequiredApprovalRoles();
        foreach ($roles as $role) {
            $field = "approval_{$role}";
            if ($this->$field === 'ditolak') {
                return $role;
            }
        }
        return null;
    }

    /**
     * Dapatkan catatan penolakan
     */
    public function getRejectionNote(): ?string
    {
        $rejectedBy = $this->getRejectedBy();
        if (!$rejectedBy) return null;
        $field = "catatan_{$rejectedBy}";
        return $this->$field;
    }

    /**
     * Cek apakah semua laporan yang dibutuhkan sudah terisi
     */
    public function isLaporanComplete(): bool
    {
        $roles = $this->getRequiredLaporanRoles();
        if (empty($roles)) return true;

        foreach ($roles as $role) {
            $field = "laporan_{$role}";
            if (empty($this->$field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Cek apakah progres bisa diselesaikan oleh BK
     */
    public function canBeClosed(): bool
    {
        // Peringatan lisan otomatis selesai
        if ($this->jenis_tindakan === 'peringatan_lisan') return true;

        // Teguran lisan otomatis selesai setelah wali kelas submit laporan
        if ($this->jenis_tindakan === 'teguran_lisan') {
            return !empty($this->laporan_walikelas);
        }

        // Harus approval complete
        if (!$this->isApprovalComplete()) return false;

        // Jika butuh surat, harus sudah dicetak
        if ($this->requiresCetakSurat() && !$this->surat_dicetak) return false;

        // Semua laporan harus terisi
        return $this->isLaporanComplete();
    }

    /**
     * Cek apakah tombol cetak surat boleh aktif
     * Surat TIDAK BOLEH AKTIF sebelum semua approval selesai
     */
    public function canPrintSurat(): bool
    {
        if (!$this->requiresCetakSurat()) return false;
        if ($this->surat_dicetak) return false; // sudah dicetak
        return $this->isApprovalComplete();
    }

    /**
     * Cek apakah role tertentu perlu melakukan approval
     */
    public function requiresApprovalFrom(string $role): bool
    {
        return in_array($role, $this->getRequiredApprovalRoles());
    }

    /**
     * Cek apakah role tertentu perlu mengisi laporan
     */
    public function requiresLaporanFrom(string $role): bool
    {
        return in_array($role, $this->getRequiredLaporanRoles());
    }

    /**
     * Cek apakah role tertentu sudah approve
     */
    public function hasApprovedBy(string $role): bool
    {
        $field = "approval_{$role}";
        return $this->$field === 'disetujui';
    }

    /**
     * Cek apakah role tertentu sudah mengisi laporan
     */
    public function hasLaporanFrom(string $role): bool
    {
        $field = "laporan_{$role}";
        return !empty($this->$field);
    }

    /**
     * Cek apakah progres bisa direvisi (status dikembalikan)
     */
    public function canBeRevised(): bool
    {
        return $this->status === 'dikembalikan';
    }

    /**
     * Reset approval fields untuk revisi
     */
    public function resetApprovals(): void
    {
        $roles = $this->getRequiredApprovalRoles();
        $data = [];
        foreach ($roles as $role) {
            $data["approval_{$role}"] = 'belum';
            $data["approval_{$role}_at"] = null;
            $data["catatan_{$role}"] = null;
            $data["dokumen_{$role}"] = null;
        }
        $this->update($data);
    }

    /**
     * Menghitung step saat ini untuk timeline visual
     */
    public function getCurrentStep(): string
    {
        if ($this->status === 'selesai') return 'selesai';
        if ($this->status === 'dikembalikan') return 'dikembalikan';
        if ($this->jenis_tindakan === 'peringatan_lisan') return 'selesai';

        $needApproval = !empty($this->getRequiredApprovalRoles());

        // Cek jika ada rejection
        if ($needApproval && $this->hasRejectedApproval()) {
            return 'dikembalikan';
        }

        if ($needApproval && !$this->isApprovalComplete()) {
            return 'menunggu_approval';
        }

        if ($this->requiresCetakSurat() && !$this->surat_dicetak) {
            return 'menunggu_cetak_surat';
        }

        if (!$this->isLaporanComplete()) {
            return 'menunggu_laporan';
        }

        return 'menunggu_selesai'; // BK bisa menutup
    }

    /**
     * Auto-update status berdasarkan kondisi saat ini
     */
    public function refreshStatus(): void
    {
        $oldStatus = $this->status;
        $step = $this->getCurrentStep();

        $newStatus = match ($step) {
            'selesai' => 'selesai',
            'dikembalikan' => 'dikembalikan',
            'menunggu_approval' => 'menunggu_approval',
            'menunggu_cetak_surat' => 'menunggu_cetak_surat',
            'menunggu_laporan' => 'menunggu_laporan',
            'menunggu_selesai' => 'menunggu_laporan', // tetap di menunggu_laporan sampai BK tutup
            default => $this->status,
        };

        // Auto-close untuk teguran lisan: otomatis SELESAI setelah wali kelas submit laporan
        if ($this->jenis_tindakan === 'teguran_lisan' && !empty($this->laporan_walikelas) && $newStatus !== 'selesai') {
            $newStatus = 'selesai';
            // Juga selesaikan pelanggaran induknya
            $this->pelanggaran?->update(['status' => 'selesai']);
        }

        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);

            // Auto-log status change via AuditService
            if (class_exists(\App\Services\AuditService::class)) {
                \App\Services\AuditService::logStatusChanged($this, $oldStatus, $newStatus);
            }
        }
    }

    // =====================
    // Accessors
    // =====================

    public function getJenisTindakanLabelAttribute(): string
    {
        return self::jenisTindakanOptions()[$this->jenis_tindakan] ?? $this->jenis_tindakan;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_approval' => 'Menunggu Persetujuan',
            'menunggu_cetak_surat' => 'Menunggu Cetak Surat',
            'menunggu_laporan' => 'Menunggu Laporan',
            'dikembalikan' => 'Dikembalikan ke BK',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'menunggu_approval' => 'bg-yellow-100 text-yellow-800',
            'menunggu_cetak_surat' => 'bg-blue-100 text-blue-800',
            'menunggu_laporan' => 'bg-purple-100 text-purple-800',
            'dikembalikan' => 'bg-red-100 text-red-800',
            'selesai' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getJenisTindakanBadgeAttribute(): string
    {
        return match ($this->jenis_tindakan) {
            'peringatan_lisan' => 'bg-gray-100 text-gray-800',
            'teguran_lisan' => 'bg-blue-100 text-blue-800',
            'home_visit' => 'bg-indigo-100 text-indigo-800',
            'pemanggilan_ortu' => 'bg-yellow-100 text-yellow-800',
            'sp1' => 'bg-orange-100 text-orange-800',
            'sp2' => 'bg-red-100 text-red-800',
            'sp3' => 'bg-red-200 text-red-900',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // =====================
    // Scopes
    // =====================

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByJenisTindakan($query, $jenis)
    {
        return $query->where('jenis_tindakan', $jenis);
    }

    /**
     * Scope: pelanggaran yang membutuhkan aksi dari walikelas tertentu
     */
    public function scopeForWalikelas($query, $userId)
    {
        return $query->whereHas('pelanggaran.siswa.kelas', function ($q) use ($userId) {
            $q->where('wali_kelas_id', $userId);
        });
    }

    /**
     * Scope: pelanggaran yang membutuhkan aksi dari kaprog tertentu
     */
    public function scopeForKaprog($query, $userId)
    {
        return $query->whereHas('pelanggaran.siswa.kelas.jurusan', function ($q) use ($userId) {
            $q->where('kaprog_id', $userId);
        })->whereIn('jenis_tindakan', ['sp1', 'sp2', 'sp3']);
    }

    /**
     * Scope: pelanggaran yang membutuhkan aksi dari waka kesiswaan
     */
    public function scopeForWaka($query)
    {
        return $query->whereIn('jenis_tindakan', ['sp2', 'sp3']);
    }

    /**
     * Scope: pelanggaran yang membutuhkan aksi dari kepala sekolah
     */
    public function scopeForKepsek($query)
    {
        return $query->whereIn('jenis_tindakan', ['sp3']);
    }

    /**
     * Scope: belum selesai
     */
    public function scopeBelumSelesai($query)
    {
        return $query->where('status', '!=', 'selesai');
    }

    /**
     * Scope: dikembalikan
     */
    public function scopeDikembalikan($query)
    {
        return $query->where('status', 'dikembalikan');
    }
}
