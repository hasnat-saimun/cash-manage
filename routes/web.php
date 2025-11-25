<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clintController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\frontController;
use App\Http\Controllers\bankManageController;


Route::get('/', function () {
    return view('dashboard');
});

//dashboard route
Route::get('/dashboard', [
  frontController::class,
   'dashboardView'])
   ->name('dashboardView');

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
  

  //bank manage route
Route::get('/bank-manage', [
    bankManageController::class,
     'bankManageView'])
     ->name('bankManageView'); 

  //bank manage save route
Route::post('/save-bank-manage', [
    bankManageController::class,
     'saveBankManage'])
     ->name('saveBankManage');

  //bank manage edit route
Route::get('/bank-manage-edit/{id}', [
    bankManageController::class,
     'bankManageEdit'])
     ->name('bankManageEdit');
     
     //bank manage update route
Route::post('/update-bank-manage', [
    bankManageController::class,
     'updateBankManage'])
     ->name('updateBankManage');

      //bank manage delete route
Route::get('/delete-bank-manage/{id}', [
    bankManageController::class,
     'deleteBankManage'])
     ->name('deleteBankManage');  

     //bank account creation route
Route::get('/bank-account-creation', [
    bankManageController::class,
     'bankAccountCreationView'])
     ->name('bankAccountCreationView'); 

     //bank account save route
Route::post('/save-bank-account', [
    bankManageController::class,
     'saveBankAccount'])
     ->name('saveBankAccount');


