<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = User::role('admin')->first();
Auth::login($admin);

$request = new Request(['draw' => 1]);
$controller = new App\Http\Controllers\InventoryController();
$response = $controller->index($request);

echo $response->getContent();
