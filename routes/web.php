<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clintController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\frontController;

Route::get('/', function () {
    return view('dashBoard');
});

//sourerce route
Route::get('/source', [
  frontController::class,
   'sourceView'])
   ->name('sourceView');

//source route save
Route::post('/save-source', [
  frontController::class,
   'saveSource'])
   ->name('saveSource');

//source edit route
Route::get('/source-edit/{id}', [
  frontController::class,
   'sourceEdit'])
   ->name('sourceEdit');

//source update route
Route::post('/update-source', [
  frontController::class,
   'updateSource'])
   ->name('updateSource');

//source delete route
Route::get('/delete-source/{id}', [
  frontController::class,
   'deleteSource'])
   ->name('deleteSource');

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

  // route for delete client
  Route::get('/delete-client/{id}',
 [    clintController::class, 
  'deleteClient'])
  ->name('deleteClient');
  // Transaction Creation Route
Route::get('/transaction-creation',
 [   transactionController::class,
  'transactionCreation'])
  ->name('transactionCreation');

  // Transaction Save Route
Route::post('/save-transaction',
 [   transactionController::class,
  'saveTransaction'])
  ->name('saveTransaction');

// Transaction List Route
Route::get('/transaction-list',
 [   transactionController::class,
  'transactionList'])
  ->name('transactionList');  

  // Transaction Edit Route
Route::get('/transaction-edit/{id}',
 [   transactionController::class,
  'transactionEdit'])
  ->name('transactionEdit');
