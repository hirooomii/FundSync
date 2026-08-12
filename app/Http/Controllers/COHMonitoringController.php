<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class COHMonitoringController extends Controller
{
    public function index()
    {
        return Inertia::render('COH/COHMonitoring');
    }

    public function fetchCOH(Request $request)
    {
        $companies = ['ROPALI', 'MOTORBELLE', 'MOTORALI', 'MOTOROBEE'];
        $results   = [];

        foreach ($companies as $company) {
            $totals = DB::table('bank_cash_account as BA')
                ->leftJoin('cms_cash_record_main as CM', function ($join) {
                    $join->on('CM.CashAccount', '=', 'BA.CashAccount')
                         ->on('CM.Company', '=', 'BA.Company');
                })
                ->where('BA.Company', 'like', "%{$company}%")
                ->select(
                    DB::raw('COUNT(DISTINCT BA.CashAccount) as total_accounts'),
                    DB::raw('COUNT(DISTINCT CASE WHEN CM.RecID IS NOT NULL THEN BA.CashAccount END) as reported'),
                    DB::raw('COUNT(DISTINCT CASE WHEN CM.RecID IS NULL THEN BA.CashAccount END) as not_reported')
                )
                ->first();

            $totalAccounts = $totals->total_accounts ?? 0;
            $reported      = $totals->reported ?? 0;
            $notReported   = $totals->not_reported ?? 0;
            $complianceRate = $totalAccounts > 0
                ? round(($reported / $totalAccounts) * 100, 2)
                : 0;

            $results[] = [
                'company'         => $company,
                'total_accounts'  => $totalAccounts,
                'reported'        => $reported,
                'not_reported'    => $notReported,
                'compliance_rate' => $complianceRate,
            ];
        }

        return response()->json([
            'status' => 200,
            'data'   => $results,
        ]);
    }
}
