<?php
$file = 'c:\wamp64\www\prs\app\Http\Controllers\Api\FieldStaffDashboardApiController.php';
$content = file_get_contents($file);

$oldBrand = '$uniqueBrands = \App\Models\Product::select(\'brand\')->distinct()->pluck(\'brand\');';
$newBrand = '$uniqueBrands = \App\Models\Brand::pluck(\'name\');';
$content = str_replace($oldBrand, $newBrand, $content);

file_put_contents($file, $content);
echo "Fixed brand in FieldStaffDashboardApiController.php\n";
