<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Get all migrations that have been run
$ranMigrations = DB::table('migrations')->pluck('migration')->toArray();

$deleted = 0;

foreach ($ranMigrations as $migrationName) {
    // We only care about migrations that create tables, usually named create_*_table
    if (preg_match('/_create_(.*)_table(s?)$/', $migrationName, $matches)) {
        $tableName = $matches[1];
        
        // Sometimes the table name in the file name is not perfectly matching, 
        // but let's parse the file content to find Schema::create('tablename'
        $filePath = database_path("migrations/{$migrationName}.php");
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            if (preg_match('/Schema::create\(\s*\'([^\']+)\'/i', $content, $createMatches)) {
                $actualTableName = $createMatches[1];
                if (!Schema::hasTable($actualTableName)) {
                    echo "Table {$actualTableName} is missing (migration: {$migrationName}). Deleting record...\n";
                    DB::table('migrations')->where('migration', $migrationName)->delete();
                    $deleted++;
                }
            }
        }
    }
}

if ($deleted > 0) {
    echo "Deleted {$deleted} migration records. Ready to migrate.\n";
} else {
    echo "No missing tables found among the ran migrations.\n";
}
