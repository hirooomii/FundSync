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
                'BD.AccountNo as AccountNo',
                'BD.AccountName as AccountName',
                'BD.BankName as Bank',
                'BD.Company as Company',
                DB::raw('BB.available_balance as AvailableBalance'),
                DB::raw("CASE WHEN BB.available_balance IS NOT NULL THEN 'Balanced' ELSE 'No Balance Data' END AS Status")
            );

        if (!empty($company)) {
            $query->where('BD.Company', 'like', "%{$company}%");
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function getAccountTransactions(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = DB::table('cms_bank_transactions')
            ->where('account_no', $accountNo)
            ->orderBy('transaction_date', 'desc');

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }

        return response()->json($query->get());
    }
}
