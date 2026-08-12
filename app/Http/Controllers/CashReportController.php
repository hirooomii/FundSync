<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashReportController extends Controller
{
    public function index()
    {
        return Inertia::render('CashReport/CashReport');
    }

    public function getCashAccounts(Request $request)
    {
        $company = $request->input('company');

        $data = DB::table('bank_cash_account')
            ->where('Company', 'like', "%{$company}%")
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function getEndingBalance(Request $request)
    {
        $cashAccount = $request->input('cash_account');

        $data = DB::table('cms_cash_record_main')
            ->where('CashAccount', $cashAccount)
            ->orderBy('CreatedAt', 'desc')
            ->first();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function saveCOHReport(Request $request)
    {
        $id = DB::table('cms_cash_record_main')->insertGetId([
            'Company'     => $request->input('Company'),
            'CashAccount' => $request->input('CashAccount'),
            'BranchCode'  => $request->input('BranchCode'),
            'Status'      => $request->input('Status'),
            'CreatedBy'   => auth()->id(),
            'CreatedAt'   => now(),
        ]);

        return response()->json([
            'status' => 200,
            'RecID'  => $id,
            'message' => 'COH report saved successfully.',
        ]);
    }
}
