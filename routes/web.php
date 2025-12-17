<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\clintController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\frontController;
use App\Http\Controllers\bankManageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MobileBankingController;
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

// Protect all feature routes: require auth and business context
Route::middleware(['auth', \App\Http\Middleware\SetBusiness::class])->group(function () {
  //sourerce route
  Route::get('/source', [frontController::class,'sourceView'])->name('sourceView');
  //source route save
  Route::post('/save-source', [frontController::class,'saveSource'])->name('saveSource');
  //source edit route
  Route::get('/source-edit/{id}', [frontController::class,'sourceEdit'])->name('sourceEdit');
  //source update route
  Route::post('/update-source', [frontController::class,'updateSource'])->name('updateSource');
  //source delete route
  Route::get('/delete-source/{id}', [frontController::class,'deleteSource'])->name('deleteSource');

  // Client Creation Routes
  Route::get('/client-creation', [clintController::class,'clientCreation'])->name('clientCreation');
  Route::post('/save-client', [clintController::class,'saveClient'])->name('saveClient');
  //client edit route
  Route::get('/client-edit/{id}', [clintController::class,'clientEdit'])->name('clientEdit');
  //client update route
  Route::post('/update-client', [clintController::class,'updateClient'])->name('updateClient');
  // route for delete client
  Route::get('/delete-client/{id}', [clintController::class,'deleteClient'])->name('deleteClient');

  // Transactions
  Route::get('/transaction-creation', [transactionController::class,'transactionCreation'])->name('transactionCreation');
  Route::post('/save-transaction', [transactionController::class,'saveTransaction'])->name('saveTransaction');
  Route::get('/transaction-list', [transactionController::class,'transactionList'])->name('transactionList');
  Route::get('/transaction-edit/{id}', [transactionController::class,'transactionEdit'])->name('transactionEdit');
  Route::get('/delete-transaction/{id}', [transactionController::class,'deleteTransaction'])->name('deleteTransaction');

  // Bank Manage
  Route::get('/bank-manage', [bankManageController::class,'bankManageView'])->name('bankManageView');
  Route::post('/save-bank-manage', [bankManageController::class,'saveBankManage'])->name('saveBankManage');
  Route::get('/bank-manage-edit/{id}', [bankManageController::class,'bankManageEdit'])->name('bankManageEdit');
  Route::post('/update-bank-manage', [bankManageController::class,'updateBankManage'])->name('updateBankManage');
  Route::get('/delete-bank-manage/{id}', [bankManageController::class,'deleteBankManage'])->name('deleteBankManage');

  // Bank Accounts
  Route::get('/bank-account-creation', [bankManageController::class,'bankAccountCreationView'])->name('bankAccountCreationView');
  Route::post('/save-bank-account', [bankManageController::class,'saveBankAccount'])->name('saveBankAccount');
  Route::get('/bank-account-edit/{id}', [bankManageController::class,'bankAccountEdit'])->name('bankAccountEdit');
  Route::post('/update-bank-account', [bankManageController::class,'updateBankAccount'])->name('updateBankAccount');
  Route::get('/delete-bank-account/{id}', [bankManageController::class,'deleteBankAccount'])->name('deleteBankAccount');

  // Bank Transactions
  Route::get('/bank-transaction', [transactionController::class,'bankTransactionCreation'])->name('bankTransactionCreation');
  Route::post('/save-bank-transaction', [transactionController::class,'saveBankTransaction'])->name('saveBankTransaction');
  Route::get('/bank-transaction-list', [transactionController::class,'bankTransactionList'])->name('bankTransactionList');
  Route::get('/bank-transaction-edit/{id}', [transactionController::class,'bankTransactionEdit'])->name('bankTransactionEdit');
  Route::get('/delete-bank-transaction/{id}', [transactionController::class,'deleteBankTransaction'])->name('deleteBankTransaction');
});


// Auth
Route::get('login', [AuthController::class,'showLogin'])->name('login');
Route::post('login', [AuthController::class,'login'])->name('auth.login');
Route::get('register', [AuthController::class,'showRegister'])->name('auth.register');
Route::post('register', [AuthController::class,'register'])->name('auth.register.post');
Route::post('logout', [AuthController::class,'logout'])->name('auth.logout');

// Protected routes with business context
Route::middleware(['auth', \App\Http\Middleware\SetBusiness::class])->group(function () {
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
    // export PDF
    Route::get('reports/client-transaction/pdf', [ReportController::class, 'clientTransactionPdf'])->name('reports.clientTransaction.pdf');

    // Business management
    Route::get('businesses', [\App\Http\Controllers\BusinessController::class, 'index'])->name('business.index');
    Route::post('businesses', [\App\Http\Controllers\BusinessController::class, 'store'])->name('business.store');
    Route::post('businesses/switch', [\App\Http\Controllers\BusinessController::class, 'switch'])->name('business.switch');

    //bank-wise transaction report
    Route::get('reports/bank-transaction', [ReportController::class, 'bankTransactionReport'])->name('reports.bankTransaction');
    // bank export CSV
    Route::get('reports/bank-transaction/export', [ReportController::class, 'bankTransactionExport'])->name('reports.bankTransaction.export');
    // bank export PDF
    Route::get('reports/bank-transaction/pdf', [ReportController::class, 'bankTransactionPdf'])->name('reports.bankTransaction.pdf');
    // export CSV
      // Capital Account (total business)
      Route::get('reports/capital-account', [ReportController::class, 'capitalAccount'])->name('reports.capitalAccount');

    // Site Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Mobile Banking (daily balance + profit)
    Route::get('mobile-banking', [MobileBankingController::class, 'index'])->name('mobile.index');
    Route::post('mobile-banking', [MobileBankingController::class, 'store'])->name('mobile.store');
    Route::post('mobile-banking/accounts', [MobileBankingController::class, 'addAccount'])->name('mobile.accounts.add');
    Route::post('mobile-banking/accounts/update', [MobileBankingController::class, 'updateAccount'])->name('mobile.accounts.update');
    Route::delete('mobile-banking/accounts/{id}', [MobileBankingController::class, 'deleteAccount'])->name('mobile.accounts.delete');
    Route::delete('mobile-banking/entries/{id}', [MobileBankingController::class, 'deleteEntry'])->name('mobile.entries.delete');
    Route::post('mobile-banking/entries/update', [MobileBankingController::class, 'updateEntry'])->name('mobile.entries.update');
    
      // Mobile Providers management
      Route::get('/mobile-banking/providers', [\App\Http\Controllers\MobileProviderController::class, 'index'])->name('mobile.providers.index');
      Route::post('/mobile-banking/providers', [\App\Http\Controllers\MobileProviderController::class, 'store'])->name('mobile.providers.store');
      Route::delete('/mobile-banking/providers/{id}', [\App\Http\Controllers\MobileProviderController::class, 'delete'])->name('mobile.providers.delete');
});

// Super Admin / Admin users management
Route::middleware(['auth', \App\Http\Middleware\AdminAccess::class])
  ->prefix('admin')
  ->name('admin.')
  ->group(function () {
    Route::get('users', [AdminUserController::class,'index'])->name('users.index');
    Route::get('users/create', [AdminUserController::class,'create'])->name('users.create');
    Route::post('users', [AdminUserController::class,'store'])->name('users.store');
    Route::get('users/{user}/edit', [AdminUserController::class,'edit'])->name('users.edit');
    Route::put('users/{user}', [AdminUserController::class,'update'])->name('users.update');
    Route::delete('users/{user}', [AdminUserController::class,'destroy'])->name('users.destroy');
    // permissions mapping
    Route::get('users/{user}/permissions', [AdminUserController::class,'permissions'])->name('users.permissions');
    Route::post('users/{user}/permissions', [AdminUserController::class,'updatePermissions'])->name('users.permissions.update');
});







