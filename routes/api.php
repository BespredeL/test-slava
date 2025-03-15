<?php

use App\Http\Controllers\Api\RowController;
use Illuminate\Support\Facades\Route;

Route::get('/rows', [RowController::class, 'index']);