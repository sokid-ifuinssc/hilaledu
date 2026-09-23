<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \Illuminate\Support\Facades\DB::select("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'siswas' 
      AND COLUMN_NAME = 'kelas_id' 
      AND REFERENCED_TABLE_NAME IS NOT NULL
");
print_r($results);
