<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo view('admin.settings.field_staff', [
        'geofence_radius' => 20,
        'ta_rate_per_km' => 10,
        'da_hq_rate' => 250,
        'da_outstation_rate' => 500,
        'hq_radius_km' => 15,
        'leaveTypes' => collect([]),
        'visitPurposes' => collect([])
    ])->render();
    echo "OK";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
