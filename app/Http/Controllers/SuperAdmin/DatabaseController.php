<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Exception;

class DatabaseController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Tampilkan halaman manajemen backup dan restore database.
     */
    public function index()
    {
        $dbInfo  = $this->backupService->getDatabaseInfo();
        $backups = $this->backupService->getBackupFiles();

        return view('superadmin.database.index', compact('dbInfo', 'backups'));
    }

    /**
     * Ekspor database ke file SQL dan langsung download.
     */
    public function export(Request $request)
    {
        try {
            $filePath = $this->backupService->exportDatabase();
            $filename = basename($filePath);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/sql',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('superadmin.database.index')
                ->with('error', 'Gagal mengekspor database: ' . $e->getMessage());
        }
    }

    /**
     * Import / pulihkan database dari file SQL yang diunggah.
     */
    public function import(Request $request)
    {
        $request->validate([
            'sql_file'       => 'required|file|max:102400', // maks 100MB
            'confirm_import' => 'accepted',
        ], [
            'sql_file.required'       => 'File SQL backup wajib dipilih.',
            'sql_file.file'           => 'File yang diunggah tidak valid.',
            'sql_file.max'            => 'Ukuran file SQL maksimal 100MB.',
            'confirm_import.accepted' => 'Anda wajib mencentang konfirmasi pemulihan database.',
        ]);

        $file = $request->file('sql_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'sql') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Format file harus berekstensi .sql');
        }

        try {
            $result = $this->backupService->importDatabase($file->getRealPath(), true);

            $msg = "Database berhasil dipulihkan! " . number_format($result['statements_count']) . " perintah SQL dieksekusi ({$result['duration_seconds']} dtk).";
            if (!empty($result['auto_backup_file'])) {
                $msg .= " Cadangan database sebelumnya disimpan otomatis sebagai: {$result['auto_backup_file']}";
            }

            return redirect()->route('superadmin.database.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memulihkan database: ' . $e->getMessage());
        }
    }

    /**
     * Pulihkan database langsung dari arsip backup lokal yang tersimpan.
     */
    public function restoreBackup(string $file)
    {
        $path = $this->backupService->getBackupPath($file);
        if (!$path) {
            return redirect()->route('superadmin.database.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        try {
            $result = $this->backupService->importDatabase($path, true);

            $msg = "Database berhasil dipulihkan dari arsip '{$file}'! " . number_format($result['statements_count']) . " query dieksekusi.";
            if (!empty($result['auto_backup_file'])) {
                $msg .= " Cadangan sebelum restore diamankan ke: {$result['auto_backup_file']}";
            }

            return redirect()->route('superadmin.database.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->route('superadmin.database.index')
                ->with('error', 'Gagal memulihkan arsip: ' . $e->getMessage());
        }
    }

    /**
     * Unduh file backup yang ada di arsip.
     */
    public function downloadBackup(string $file)
    {
        $path = $this->backupService->getBackupPath($file);
        if (!$path) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Hapus file backup dari arsip.
     */
    public function deleteBackup(string $file)
    {
        $deleted = $this->backupService->deleteBackup($file);
        if ($deleted) {
            return redirect()->route('superadmin.database.index')
                ->with('success', "File backup '{$file}' berhasil dihapus.");
        }

        return redirect()->route('superadmin.database.index')
            ->with('error', "Gagal menghapus file backup '{$file}'.");
    }
}
