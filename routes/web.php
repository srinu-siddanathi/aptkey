<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ping-admin', function () {
    return 'ADMIN OK';
});
