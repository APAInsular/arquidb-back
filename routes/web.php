<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';

Route::get('/check-archivo', [App\Http\Controllers\ArchivoController::class, 'verificarArchivo']);
