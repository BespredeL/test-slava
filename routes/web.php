<?php

use App\Http\Controllers\Import\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/import', [ImportController::class, 'showUploadForm'])->name('import.form');
Route::post('/import', [ImportController::class, 'import'])->name('import.process');