<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clintController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\frontController;
use App\Http\Controllers\bankManageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
  if (\App\Models\User::count() === 0) {
    return redirect()->route('auth.register');
  }
  return redirect()->route('login');
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

  //transaction delete route
  Route::get('/delete-transaction/{id}',
 [   transactionController::class,
  'deleteTransaction'])
  ->name('deleteTransaction');

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

      //bank account edit route
Route::get('/bank-account-edit/{id}', [
    bankManageController::class,
      'bankAccountEdit'])
      ->name('bankAccountEdit');

      //bank account update route
Route::post('/update-bank-account', [
    bankManageController::class,
      'updateBankAccount'])
      ->name('updateBankAccount');

      //bank account delete route
Route::get('/delete-bank-account/{id}', [
    bankManageController::class,
      'deleteBankAccount'])
      ->name('deleteBankAccount');

      //bank transaction creation route
Route::get('/bank-transaction', [
    transactionController::class,
      'bankTransactionCreation'])
      ->name('bankTransactionCreation');

      //bank transaction save route
Route::post('/save-bank-transaction', [
    transactionController::class,
      'saveBankTransaction'])
      ->name('saveBankTransaction');

      //bank transaction list route
Route::get('/bank-transaction-list', [
    transactionController::class,
      'bankTransactionList'])
      ->name('bankTransactionList');
      
      //bank transaction edit route
Route::get('/bank-transaction-edit/{id}', [
    transactionController::class,
      'bankTransactionEdit'])
      ->name('bankTransactionEdit');

      //bank transaction delete route
Route::get('/delete-bank-transaction/{id}', [
    transactionController::class,
      'deleteBankTransaction'])
      ->name('deleteBankTransaction');


// Auth
Route::get('login', [AuthController::class,'showLogin'])->name('login');
Route::post('login', [AuthController::class,'login'])->name('auth.login');
Route::get('register', [AuthController::class,'showRegister'])->name('auth.register');
Route::post('register', [AuthController::class,'register'])->name('auth.register.post');
Route::post('logout', [AuthController::class,'logout'])->name('auth.logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('dashboard-view', function () {
        return redirect()->route('dashboard');
    })->name('dashboardView');

    Route::get('profile', [ProfileController::class,'show'])->name('profile.show');
    Route::post('profile/update', [ProfileController::class,'updateProfile'])->name('profile.update');
    Route::post('profile/password', [ProfileController::class,'changePassword'])->name('profile.password');
    Route::post('profile/avatar', [ProfileController::class,'updateAvatar'])->name('profile.avatar');

    // Reports: client-wise transaction report
    Route::get('reports/client-transaction', [ReportController::class, 'index'])->name('reports.clientTransaction');
    // export CSV
    Route::get('reports/client-transaction/export', [ReportController::class, 'export'])->name('reports.clientTransaction.export');
});







