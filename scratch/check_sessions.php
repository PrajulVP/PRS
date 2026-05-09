<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo Schema::hasTable('sessions') ? "Sessions table exists\n" : "Sessions table MISSING\n";

if (Schema::hasTable('sessions')) {
    $count = DB::table('sessions')->count();
    echo "Total sessions in table: $count\n";
}
