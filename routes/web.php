<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\BankDepositoryController;
use App\Http\Controllers\PaymentScheduleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
  
    Route::get('/cashaccount', [CashAccountController::class, 'index'])
        ->name('cashaccount');

    Route::get('/retrieve-cashaccount', [CashAccountController:: class, 'retrieveCashAccount'])
        ->name('retrieve.cashaccount');

    Route::post('/bind-account', [CashAccountController::class, 'bindAccount'])
        ->name('bind.account');

    Route::post('/activate-deactivate', [CashAccountController:: class, 'accountStatus'])
        ->name('activate.deactivate');
        
    Route::post('/merge-cashaccount', [CashAccountController:: class, 'mergeCashAccount'])
        ->name('merge.cashaccount');

    Route::post('/api-cashaccount', [CashAccountController:: class, 'fetchCashAccounts'])
        ->name('api.cashaccount');

});

Route::middleware(['auth', 'verified'])->group(function () {
  
    Route::get('/depositorybank', [BankDepositoryController::class, 'index'])
        ->name('depositorybank');

    Route::get('/retrieve-account/{accountNo}', [BankDepositoryController:: class, 'retrieveAccountDetails'])
        ->name('retrieve.account');

    Route::get('/retrieve-depository', [BankDepositoryController:: class, 'retrieveBankDepository'])
        ->name('retrieve.depository');

    Route::get('/retrieve-soa/{accountNo}', [BankDepositoryController:: class, 'retrieveAccountSOA'])
        ->name('retrieve.soa');

    Route::get('/bank-signatories/{accountNo}', [BankDepositoryController::class, 'bankSignatories'])
        ->name('bank.signatories');

    Route::get('/check-cashaccount/{accountNo}', [BankDepositoryController::class, 'accountCashAccount'])
        ->name('check.cashaccount');

    Route::get('/retrieve-transaction/{accountNo}', [BankDepositoryController:: class, 'retrieveAccountTransaction'])
        ->name('retrieve.transaction');

    Route::post('/create-depository', [BankDepositoryController::class, 'saveDepositoryBank'])
        ->name('create.depository');

    Route::post('/update-depository', [BankDepositoryController::class, 'updateDepositoryBank'])
        ->name('update.depository');

    Route::post('/bank-status', [BankDepositoryController::class, 'updateBankStatus'])
        ->name('bank.status');

    Route::post('/signatories-status', [BankDepositoryController::class, 'updateSignatoryStatus'])
        ->name('signatories.status');

    Route::post('/insert-signatories', [BankDepositoryController::class, 'insertSignatory'])
        ->name('insert.signatories');

    Route::post('/insert-soa', [BankDepositoryController::class, 'insertStatementofAccount'])
        ->name('insert.soa');

    Route::post('/insert-transactions', [BankDepositoryController::class, 'insertBalanceTransactions'])
        ->name('insert.transactions'); 

    Route::post('/approve-soa', [BankDepositoryController::class, 'approveSOA'])
        ->name('approve.soa');

    Route::post('/disapprove-soa', [BankDepositoryController::class, 'disapproveSOA'])
        ->name('disapprove.soa');

    Route::post('/import-transaction', [BankDepositoryController::class, 'importTransaction'])
        ->name('import.transaction');

});

Route::middleware(['auth', 'verified'])->group(function () {
  
    Route::get('/payment-schedule-calendar', [PaymentScheduleController::class, 'index'])
        ->name('payment.schedule.calendar');

    Route::post('/payment-schedule-pending', [PaymentScheduleController::class, 'getDatesPending'])
        ->name('payment.schedule.pending');

    Route::post('/due-dates', [PaymentScheduleController::class, 'getDueDates'])
        ->name('due.dates');

    Route::post('/update-funding', [PaymentScheduleController::class, 'updateFundingStatus'])
        ->name('update.funding');

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users', [ProfileController::class, 'fetchUsers'])->name('users');
});

require __DIR__.'/auth.php';
