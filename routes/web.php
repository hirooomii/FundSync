<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\BankDepositoryController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReconcilliationController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ApprovalMatrixController;
use App\Http\Controllers\CashPositionController;
use App\Http\Controllers\FundTransferController;
use App\Http\Controllers\MultipleReconController;
use App\Http\Controllers\RemarkLogsController;
use App\Http\Controllers\UnbindLogsController;
use App\Http\Controllers\MultipleLogsController;
use App\Http\Controllers\OverallCOHController;
use App\Http\Controllers\COHMonitoringController;
use App\Http\Controllers\ListOfBankController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SoaApprovalController;
use App\Http\Controllers\UnpostedBookController;
use App\Http\Controllers\FTAController;
use App\Http\Controllers\FTRequestController;
use App\Http\Controllers\AnswerApprovalController;
use App\Http\Controllers\AcumaticaPassbookController;
use App\Http\Controllers\BalancesDashboardController;
use App\Http\Controllers\DailyTransactionController;
use App\Http\Controllers\TransactionOrderingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CashReportController;
use App\Http\Controllers\CreateFTAFController;
use App\Http\Controllers\GenerateReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account-summaries', [DashboardController::class, 'getBankAccountSummaries']);
    Route::get('/company-balances', [DashboardController::class, 'companyTotalBalance']);
    Route::get('/bank-balances', [DashboardController::class, 'bankBalances']);
    Route::get('/recon-status', [DashboardController::class, 'reconStatus']);
    Route::get('/recon-status-count', [DashboardController::class, 'getReconStatusCount']);
    Route::get('/pending-soa', [DashboardController::class, 'getPendingSOA']);
    Route::post('/pending-checks', [DashboardController::class, 'getAllPendingChecks']);
    Route::post('/pending-online-payment', [DashboardController::class, 'getAllPendingOnlinePayment']);
    Route::post('/pending-cash-payment', [DashboardController::class, 'getAllPendingCashPayment']);
    Route::get('/transaction-history', [DashboardController::class, 'getTransactionHistory']);
    Route::post('/api/cms/bank-details/transaction-history', [DashboardController::class, 'getTransactionHistory']);
    Route::get('/cm1-acumatica-summary', [DashboardController::class, 'getCM1AcumaticaSummary']);
    Route::get('/cm2-acumatica-summary', [DashboardController::class, 'getCM2AcumaticaSummary']);
    Route::get('/cm3-acumatica-summary', [DashboardController::class, 'getCM3AcumaticaSummary']);
    Route::get('/cm4-acumatica-summary', [DashboardController::class, 'getCM4AcumaticaSummary']);
    Route::get('/reconcilliation', [ReconcilliationController::class, 'index'])->name('reconcilliation');
});


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


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('voucher.list');
    Route::get('/voucher-logs', [VoucherController::class, 'logs'])->name('voucher.logs');
    Route::get('/get-vouchers', [VoucherController::class, 'getVouchers']);
    Route::get('/get-voucher-logs', [VoucherController::class, 'getVoucherLogs']);

    Route::get('/approval-matrix', [ApprovalMatrixController::class, 'index'])->name('approval.matrix');
    Route::get('/get-approval-matrix', [ApprovalMatrixController::class, 'getMatrix']);
    Route::post('/save-approval-matrix', [ApprovalMatrixController::class, 'saveMatrix']);
    Route::delete('/delete-approval-matrix/{id}', [ApprovalMatrixController::class, 'deleteMatrix']);

    Route::get('/cash-position', [CashPositionController::class, 'index'])->name('cash.position');
    Route::get('/get-cash-positions', [CashPositionController::class, 'getCashPositions']);
    Route::post('/save-cash-position', [CashPositionController::class, 'saveCashPosition']);

    Route::get('/fund-transfers', [FundTransferController::class, 'index'])->name('fund.transfers');
    Route::get('/get-fund-transfers', [FundTransferController::class, 'getFundTransfers']);
    Route::get('/fund-transfer-details/{id}', [FundTransferController::class, 'getFundTransferDetails']);
    Route::post('/approve-fund-transfer', [FundTransferController::class, 'approveFundTransfer']);

    Route::get('/multiple-approval', [MultipleReconController::class, 'index'])->name('multiple.approval');
    Route::get('/get-recon-groups', [MultipleReconController::class, 'getReconGroups']);
    Route::get('/recon-details/{multipleId}', [MultipleReconController::class, 'getReconDetails']);
    Route::post('/approve-recon', [MultipleReconController::class, 'approveRecon']);
    Route::post('/disapprove-recon', [MultipleReconController::class, 'disapproveRecon']);

});

Route::middleware(['auth', 'verified'])->group(function () {

    // Logs
    Route::get('/remark-logs', [RemarkLogsController::class, 'index'])->name('remark.logs');
    Route::get('/get-remark-logs', [RemarkLogsController::class, 'getRemarkLogs']);

    Route::get('/unbind-logs', [UnbindLogsController::class, 'index'])->name('unbind.logs');
    Route::get('/get-unbind-logs', [UnbindLogsController::class, 'getUnbindLogs']);

    // Reconciliation logs
    Route::get('/multiple-logs', [MultipleLogsController::class, 'index'])->name('multiple.logs');
    Route::get('/get-multiple-logs', [MultipleLogsController::class, 'getMultipleLogs']);
    Route::get('/preview-bank-details', [MultipleLogsController::class, 'previewBankDetails']);
    Route::get('/preview-booking-details', [MultipleLogsController::class, 'previewBookingDetails']);

    // COH
    Route::get('/overall-coh', [OverallCOHController::class, 'index'])->name('overall.coh');
    Route::get('/fetch-overall-coh', [OverallCOHController::class, 'fetchOverallCOH']);
    Route::get('/get-txn-details', [OverallCOHController::class, 'getTxnDetails']);
    Route::get('/get-denomination', [OverallCOHController::class, 'getDenomination']);

    Route::get('/coh-monitoring', [COHMonitoringController::class, 'index'])->name('coh.monitoring');
    Route::get('/fetch-coh', [COHMonitoringController::class, 'fetchCOH']);

    // List of Bank
    Route::get('/list-of-bank', [ListOfBankController::class, 'index'])->name('list.of.bank');
    Route::get('/get-bank-list', [ListOfBankController::class, 'getList']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/get-notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/mark-notification-read', [NotificationController::class, 'markAsRead']);
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);

    // SOA Approval
    Route::get('/soa-approval', [SoaApprovalController::class, 'index'])->name('soa.approval');
    Route::get('/get-pending-soas', [SoaApprovalController::class, 'getAllPendingSoas']);
    Route::post('/approve-soa-entry', [SoaApprovalController::class, 'approveSoa']);
    Route::post('/disapprove-soa-entry', [SoaApprovalController::class, 'disapproveSoa']);
    Route::post('/return-soa', [SoaApprovalController::class, 'returnSoa']);
    Route::get('/get-soa-transactions', [SoaApprovalController::class, 'getSoaTransactions']);

    // Unposted Book
    Route::get('/unposted-book', [UnpostedBookController::class, 'index'])->name('unposted.book');
    Route::get('/get-unposted-entries', [UnpostedBookController::class, 'getUnpostedEntries']);
    Route::post('/save-note', [UnpostedBookController::class, 'saveNote']);
    Route::get('/get-comments', [UnpostedBookController::class, 'getComments']);

    // FTA List
    Route::get('/fta-list', [FTAController::class, 'index'])->name('fta.list');
    Route::get('/get-fta-vouchers', [FTAController::class, 'getVouchers']);
    Route::get('/get-fta-logs', [FTAController::class, 'getFtaLogs']);

    // FT Request
    Route::get('/ft-request', [FTRequestController::class, 'index'])->name('ft.request');
    Route::get('/get-ft-requests', [FTRequestController::class, 'getRequests']);

    // Answer Approval
    Route::get('/answer-approval', [AnswerApprovalController::class, 'index'])->name('answer.approval');
    Route::get('/get-approval-queue', [AnswerApprovalController::class, 'getApprovalQueue']);
    Route::post('/approve-request', [AnswerApprovalController::class, 'approveRequest']);
    Route::post('/disapprove-request', [AnswerApprovalController::class, 'disapproveRequest']);

    // Acumatica Passbook
    Route::get('/acumatica-passbook', [AcumaticaPassbookController::class, 'index'])->name('acumatica.passbook');
    Route::get('/get-passbook-entries', [AcumaticaPassbookController::class, 'getEntries']);
    Route::post('/save-passbook-note', [AcumaticaPassbookController::class, 'saveNote']);
    Route::get('/get-passbook-comments', [AcumaticaPassbookController::class, 'getComments']);

    // Balances Dashboard
    Route::get('/balances-dashboard', [BalancesDashboardController::class, 'index'])->name('balances.dashboard');
    Route::get('/get-accounts-by-company', [BalancesDashboardController::class, 'getAccountsByCompany']);
    Route::get('/get-balance-summary', [BalancesDashboardController::class, 'getBalanceSummary']);
    Route::get('/get-account-transactions', [BalancesDashboardController::class, 'getAccountTransactions']);

    // Daily Transaction
    Route::get('/daily-transaction', [DailyTransactionController::class, 'index'])->name('daily.transaction');
    Route::get('/get-daily-transactions', [DailyTransactionController::class, 'getTransactions']);
    Route::get('/get-acumatica-entries', [DailyTransactionController::class, 'getAcumaticaEntries']);
    Route::post('/bind-transaction', [DailyTransactionController::class, 'bindTransaction']);
    Route::get('/recon-companies', [DailyTransactionController::class, 'getReconCompanies']);
    Route::get('/recon-banks', [DailyTransactionController::class, 'getReconBanks']);
    Route::get('/recon-accounts', [DailyTransactionController::class, 'getReconAccounts']);

    // Transaction Ordering
    Route::get('/transaction-ordering', [TransactionOrderingController::class, 'index'])->name('transaction.ordering');
    Route::get('/get-transaction-groups', [TransactionOrderingController::class, 'getGroups']);
    Route::get('/preview-transaction-order', [TransactionOrderingController::class, 'previewOrder']);
    Route::post('/save-transaction-order', [TransactionOrderingController::class, 'saveOrder']);

    // Branch
    Route::get('/branch-tagging', [BranchController::class, 'tagging'])->name('branch.tagging');
    Route::get('/branch-coh', [BranchController::class, 'branchCoh'])->name('branch.coh');
    Route::get('/branch-report', [BranchController::class, 'branchReport'])->name('branch.report');
    Route::get('/branch-reconciliation', [BranchController::class, 'reconciliation'])->name('branch.reconciliation');
    Route::get('/get-branch-list', [BranchController::class, 'getBranchList']);
    Route::get('/get-branch-tagging', [BranchController::class, 'getBranchTagging']);
    Route::post('/save-branch-tagging', [BranchController::class, 'saveBranchTagging']);
    Route::delete('/delete-branch-tagging/{id}', [BranchController::class, 'deleteBranchTagging']);
    Route::get('/get-branch-coh-data', [BranchController::class, 'getBranchCOH']);

    // Cash Report
    Route::get('/cash-report', [CashReportController::class, 'index'])->name('cash.report');
    Route::get('/get-cash-accounts', [CashReportController::class, 'getCashAccounts']);
    Route::get('/get-ending-balance', [CashReportController::class, 'getEndingBalance']);
    Route::post('/save-coh-report', [CashReportController::class, 'saveCOHReport']);

    // Create FTAF
    Route::get('/create-ftaf', [CreateFTAFController::class, 'index'])->name('create.ftaf');
    Route::get('/get-rtof-list', [CreateFTAFController::class, 'getList']);

    // Generate Report
    Route::get('/generate-report', [GenerateReportController::class, 'index'])->name('generate.report');
    Route::get('/generate-transaction-report', [GenerateReportController::class, 'getReportData']);

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users', [ProfileController::class, 'fetchUsers'])->name('users');
});

require __DIR__.'/auth.php';
