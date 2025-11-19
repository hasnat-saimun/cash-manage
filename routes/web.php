<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clintController;

Route::get('/', function () {
    return view('dashBoard');
});

// Client Creation Route
Route::get('/client-creation',
 [
    clintController::class,
  'clientCreation'])
  ->name('clientCreation');