<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BalancesDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('BalancesDashboard/BalancesDashboard');
    }

    public function getAccountsByCompany(Request $request)
    {
        $company = $request->input('company');

        $data = DB::table('cms_bank_depository')
            ->where('Company', 'like', "%{$company}%")
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function getBalanceSummary(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_bank_depository as BD')
            ->leftJoin(
                DB::raw('(
                    SELECT account_no, available_balance
                    FROM (
                        SELECT account_no, available_balance,
                               ROW_NUMBER() OVER (PARTITION BY account_no ORDER BY transaction_date DESC) AS rn
                        FROM cms_bank_balances
                    ) AS ranked
                    WHERE rn = 1
                ) AS BB'),
                'BB.account_no',
                '=',
                'BD.AccountNo'
            )
            ->select(
                'BD.AccountNo as account_no',
                'BD.AccountName as account_name',
                'BD.BankName as bank',
                'BD.Company as company',
                'BB.available_balance'
            );

        if (!empty($company)) {
            $query->where('BD.Company', 'like', "%{$company}%");
        }

        $data = $query->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function getAccountTransactions(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $data = DB::table('cms_bank_transactions')
            ->where('account_no', $accountNo)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return response()->json($data);
    }
}
