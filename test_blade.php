<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $compiler = app('blade.compiler');
    $path = resource_path('views/partials/dashboard_content.blade.php');
    $content = file_get_contents($path);
    $compiled = $compiler->compileString($content);
    echo "Compiled successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
