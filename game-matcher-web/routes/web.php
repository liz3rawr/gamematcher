<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', [GameController::class, 'index'])->name('proses.prediksi');

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/halo', function () {
//     return 'ini adalah home';
// });
