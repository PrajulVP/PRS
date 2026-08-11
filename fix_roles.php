<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u1 = App\Models\User::find(101);
if($u1) {
    $u1->assignRole('fieldstaff');
    echo "101 assigned. ";
}
$u2 = App\Models\User::find(122);
if($u2) {
    $u2->assignRole('fieldstaff');
    echo "122 assigned.";
}
