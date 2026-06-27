<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

// Form submit di Blade baru akan mengarah ke route ini secara otomatis
Route::get('/', [GameController::class, 'index'])->name('proses.prediksi');



// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/halo', function () {
//     return 'ini adalah home';
// });
