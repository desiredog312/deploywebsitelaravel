<?php

use App\Http\Controllers\ScrabbleSolverController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/solve', [ScrabbleSolverController::class, 'solve']);
Route::post('/autocomplete', [ScrabbleSolverController::class, 'autocomplete']);
