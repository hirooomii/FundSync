<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DailyTransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('DailyTransaction/DailyTransaction');
    }

    public function getTransactions(Request $request)
    {
        $accountNo = $request->input('account_no');
        $date      = $request->input('date');

        $data = DB::table('cms_bank_transactions')
            ->where('account_no', $accountNo)
            ->whereDate('transaction_date', $date)
            ->orderBy('transaction_date', 'asc')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function getAcumaticaEntries(Request $request)
    {
        $accountNo = $request->input('account_no');
        $date      = $request->input('date');

        $data = DB::table('cms_cashaccount_details')
            ->where('CashAccount', $accountNo)
            ->whereDate('TransactionDate', $date)
            ->orderBy('TransactionDate', 'asc')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function bindTransaction(Request $request)
    {
        $bankTransactionId = $request->input('bank_transaction_id');
        $acumaticaRef      = $request->input('acumatica_ref');

        DB::table('cms_bank_transactions')
            ->where('id', $bankTransactionId)
            ->update(['docref' => $acumaticaRef]);

        return response()->json([
            'status'  => 200,
            'message' => 'Transaction bound successfully.',
        ]);
    }

    public function getReconCompanies()
    {
        $companies = DB::table('cms_bank_depository')
            ->whereNotNull('Company')
            ->where('Company', '!=', '')
            ->distinct()
            ->orderBy('Company')
            ->pluck('Company');

        return response()->json($companies);
    }

    public function getReconBanks(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_bank_depository')
            ->whereNotNull('BankName')
            ->where('BankName', '!=', '')
            ->distinct()
            ->orderBy('BankName');

        if ($company) {
            $query->where('Company', $company);
        }

        return response()->json($query->pluck('BankName'));
    }

    public function getReconAccounts(Request $request)
    {
        $company  = $request->input('company');
        $bankName = $request->input('bank_name');

        $query = DB::table('cms_bank_depository')
            ->whereNotNull('AccountNo')
            ->where('AccountNo', '!=', '')
            ->orderBy('AccountNo');

        if ($company)  $query->where('Company', $company);
        if ($bankName) $query->where('BankName', $bankName);

        return response()->json($query->get(['AccountNo', 'AccountName']));
    }
}
