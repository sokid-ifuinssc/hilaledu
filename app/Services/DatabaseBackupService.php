<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Exception;
use Throwable;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    /**
     * Dapatkan informasi detail mengenai status database saat ini.
     */
    public function getDatabaseInfo(): array
    {
        $dbName = config('database.connections.mysql.database');
        $host   = config('database.connections.mysql.host');
        $port   = config('database.connections.mysql.port', 3306);

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $tableList = [];
        $totalRows = 0;

        // Query ukuran tabel dari information_schema
        $sizes = [];
        try {
            $sizes = DB::select("
                SELECT table_name AS `table`,
                       round(((data_length + index_length) / 1024 / 1024), 2) AS `size_mb`,
                       table_rows AS `rows`
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);
        } catch (\Throwable $e) {}

        $sizeMap = [];
        $totalSizeMb = 0;
        foreach ($sizes as $s) {
            $sizeMap[$s->table] = [
                'size_mb' => (float)$s->size_mb,
                'rows'    => (int)$s->rows,
            ];
            $totalSizeMb += (float)$s->size_mb;
        }

        foreach ($tables as $tbl) {
            $tName = array_values((array)$tbl)[0];
            $rCount = $sizeMap[$tName]['rows'] ?? DB::table($tName)->count();
            $sMb    = $sizeMap[$tName]['size_mb'] ?? 0;
            $totalRows += $rCount;

            $tableList[] = [
                'name'    => $tName,
                'rows'    => $rCount,
                'size_mb' => $sMb,
            ];
        }

        $version = 'MySQL';
        try {
            $versionRow = DB::selectOne('SELECT VERSION() AS ver');
            if ($versionRow && !empty($versionRow->ver)) {
                $version = $versionRow->ver;
            }
        } catch (\Throwable $e) {}

        return [
            'database'       => $dbName,
            'host'           => $host,
            'port'           => $port,
            'mysql_version'  => $version,
            'tables_count'   => count($tableList),
            'total_size_mb'  => round($totalSizeMb, 2),
            'total_rows'     => $totalRows,
            'tables'         => $tableList,
        ];
    }

    /**
     * Ekspor seluruh struktur dan data database menjadi file SQL dump.
     */
    public function exportDatabase(?string $customFilename = null): string
    {
        @set_time_limit(600);
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '512M');

        $dbName = config('database.connections.mysql.database');
        $dateStr = date('Y-m-d_His');
        $filename = $customFilename ?: "backup_{$dbName}_{$dateStr}.sql";
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new Exception("Gagal membuat file backup di: {$filePath}");
        }

        $pdo = DB::connection()->getPdo();
        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

        // Header SQL
        $header = "-- ========================================================\n"
                . "-- HilalEdu - Sistem Informasi Akademik Terpadu\n"
                . "-- Database Backup Dump\n"
                . "-- ========================================================\n"
                . "-- Database    : `{$dbName}`\n"
                . "-- Dibuat Pada : " . date('Y-m-d H:i:s') . "\n"
                . "-- Total Tabel : " . count($tables) . "\n"
                . "-- Host        : " . config('database.connections.mysql.host') . "\n"
                . "-- ========================================================\n\n"
                . "SET FOREIGN_KEY_CHECKS=0;\n"
                . "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n"
                . "SET time_zone = '+00:00';\n"
                . "SET NAMES utf8mb4;\n\n";

        fwrite($handle, $header);

        foreach ($tables as $tbl) {
            $tableName = array_values((array)$tbl)[0];

            // Dapatkan Struktur Tabel
            $createTableObj = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
            $createTableSql = $createTableObj->{'Create Table'} ?? '';

            $tableHeader = "-- --------------------------------------------------------\n"
                         . "-- Struktur Tabel: `{$tableName}`\n"
                         . "-- --------------------------------------------------------\n"
                         . "DROP TABLE IF EXISTS `{$tableName}`;\n"
                         . $createTableSql . ";\n\n";
            fwrite($handle, $tableHeader);

            // Dump Data Baris Per Baris menggunakan Chunk
            $rowCount = DB::table($tableName)->count();
            if ($rowCount > 0) {
                fwrite($handle, "-- Dumping Data: `{$tableName}` ({$rowCount} baris)\n");

                DB::table($tableName)->orderBy(DB::raw('1'))->chunk(300, function ($rows) use ($handle, $pdo, $tableName) {
                    if ($rows->isEmpty()) return;

                    $columns = array_keys((array)$rows->first());
                    $quotedCols = array_map(fn($col) => "`{$col}`", $columns);
                    $colsStr = implode(', ', $quotedCols);

                    $valueLines = [];
                    foreach ($rows as $row) {
                        $escaped = [];
                        foreach ((array)$row as $val) {
                            if (is_null($val)) {
                                $escaped[] = 'NULL';
                            } elseif (is_bool($val)) {
                                $escaped[] = $val ? '1' : '0';
                            } else {
                                $escaped[] = $pdo->quote((string)$val);
                            }
                        }
                        $valueLines[] = '(' . implode(', ', $escaped) . ')';
                    }

                    fwrite($handle, "INSERT INTO `{$tableName}` ({$colsStr}) VALUES\n" . implode(",\n", $valueLines) . ";\n\n");
                });
            }
        }

        // Footer SQL
        $footer = "SET FOREIGN_KEY_CHECKS=1;\n\n"
                . "-- ========================================================\n"
                . "-- Ekspor Database Selesai Pada: " . date('Y-m-d H:i:s') . "\n"
                . "-- ========================================================\n";
        fwrite($handle, $footer);
        fclose($handle);

        return $filePath;
    }

    /**
     * Impor file SQL ke database dengan keamanan auto-backup terlebih dahulu.
     */
    public function importDatabase(string $sqlFilePath, bool $createAutoBackup = true): array
    {
        @set_time_limit(900);
        @ini_set('max_execution_time', '900');
        @ini_set('memory_limit', '1024M');

        if (!File::exists($sqlFilePath)) {
            throw new Exception("File SQL tidak ditemukan pada: {$sqlFilePath}");
        }

        $autoBackupFile = null;
        if ($createAutoBackup) {
            // Buat backup cadangan otomatis sebelum melakukan import untuk keamanan data
            try {
                $autoBackupName = "auto_safety_backup_" . date('Y-m-d_His') . ".sql";
                $autoBackupFile = $this->exportDatabase($autoBackupName);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal membuat safety backup: " . $e->getMessage());
            }
        }

        $startTime = microtime(true);
        $handle = fopen($sqlFilePath, 'r');
        if (!$handle) {
            throw new Exception("Tidak dapat membaca file SQL.");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');

        $buffer = '';
        $statementCount = 0;
        $inComment = false;

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                // Tangani komentar multi-baris /* ... */
                if ($inComment) {
                    if (str_contains($trimmed, '*/')) {
                        $inComment = false;
                    }
                    continue;
                }

                // Lewati baris kosong atau komentar baris tunggal (-- atau #)
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                    continue;
                }

                // Cek awal komentar blok murni (bukan direktif MySQL /*!)
                if (str_starts_with($trimmed, '/*') && !str_starts_with($trimmed, '/*!')) {
                    if (!str_contains($trimmed, '*/')) {
                        $inComment = true;
                    }
                    continue;
                }

                $buffer .= $line;

                // Jika baris diakhiri titik koma, eksekusi query
                if (str_ends_with($trimmed, ';')) {
                    DB::unprepared($buffer);
                    $statementCount++;
                    $buffer = '';
                }
            }

            // Jalankan sisa query jika ada
            if (trim($buffer) !== '') {
                DB::unprepared($buffer);
                $statementCount++;
            }
        } finally {
            fclose($handle);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'status'             => 'success',
            'statements_count'   => $statementCount,
            'duration_seconds'   => $duration,
            'auto_backup_file'   => $autoBackupFile ? basename($autoBackupFile) : null,
        ];
    }

    /**
     * Dapatkan daftar seluruh file backup yang ada di direktori storage.
     */
    public function getBackupFiles(): array
    {
        if (!File::exists($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'sql') continue;

            $filename = $file->getFilename();
            $bytes = $file->getSize();
            $time = Carbon::createFromTimestamp($file->getMTime());

            $backups[] = [
                'filename'    => $filename,
                'path'        => $file->getPathname(),
                'size_bytes'  => $bytes,
                'size_human'  => $this->formatBytes($bytes),
                'created_at'  => $time,
                'is_safety'   => str_starts_with($filename, 'auto_safety_backup_'),
            ];
        }

        // Urutkan dari yang paling baru
        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    /**
     * Hapus file backup tertentu.
     */
    public function deleteBackup(string $filename): bool
    {
        $sanitized = basename($filename);
        $path = $this->backupDir . DIRECTORY_SEPARATOR . $sanitized;

        if (File::exists($path)) {
            return File::delete($path);
        }
        return false;
    }

    /**
     * Dapatkan path lengkap file backup yang valid.
     */
    public function getBackupPath(string $filename): ?string
    {
        $sanitized = basename($filename);
        $path = $this->backupDir . DIRECTORY_SEPARATOR . $sanitized;

        return File::exists($path) ? $path : null;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
