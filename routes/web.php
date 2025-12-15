<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ping-admin', function () {
    return 'ADMIN OK';
});

Route::get('/bknd-test', function () {
    return 'BKND TEST OK';
});

Route::get('/mgr-test', function () {
    return 'MGR TEST OK';
});
