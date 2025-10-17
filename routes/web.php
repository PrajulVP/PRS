<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admindashboard', function () {
    return view('admin/index');
});

Route::get('/adminlogin', function () {
    return view('admin/login');
});