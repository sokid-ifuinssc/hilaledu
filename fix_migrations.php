<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('migrations')->where('migration', 'like', '%create_kategori_pelanggarans%')->delete();
DB::table('migrations')->where('migration', 'like', '%create_jenis_pelanggarans%')->delete();

echo "Deleted migration records\n";
