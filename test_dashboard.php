<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$fieldStaffUser = \App\Models\User::where('role', 'fieldstaff')->first();
$token = auth('api')->login($fieldStaffUser);

$request = Illuminate\Http\Request::create('/api/field-staff/dashboard', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $token);

$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . $response->getContent() . "\n";
