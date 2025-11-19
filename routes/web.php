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

Route::post('/save-client',
 [
    clintController::class,
  'saveClient'])
  ->name('saveClient');


//client edit route
Route::get('/client-edit/{id}',
 [    clintController::class,
  'clientEdit'])
  ->name('clientEdit');

  //client update route
   Route::post('/update-client',
 [    clintController::class,
  'updateClient'])
  ->name('updateClient');