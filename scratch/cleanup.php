<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\DB::table('migrations')->where('migration', '2026_09_02_131828_add_brand_back_to_products_table')->delete();
echo 'Cleaned up!';
