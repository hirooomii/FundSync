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
}
