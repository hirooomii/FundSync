<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GenerateReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/GenerateReport');
    }

    public function getReportData(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');
        $source    = $request->input('source', 'bank');

        if ($source === 'acumatica') {
            $data = DB::table('cms_cashaccount_details')
                ->where('CashAccount', $accountNo)
                ->whereBetween('TransactionDate', [$dateFrom, $dateTo])
                ->orderBy('TransactionDate', 'asc')
                ->get();
        } else {
            $data = DB::table('cms_bank_transactions')
                ->where('account_no', $accountNo)
                ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->orderBy('transaction_date', 'asc')
                ->get();
        }

        return response()->json([
            'status' => 200,
            'source' => $source,
            'data'   => $data,
        ]);
    }
}
