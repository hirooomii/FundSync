<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OverallCOHController extends Controller
{
    public function index()
    {
        return Inertia::render('COH/OverallCOH');
    }

    public function fetchOverallCOH(Request $request)
    {
        $company   = $request->input('company');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = DB::table('cms_cash_record_main')
            ->select(
                'Company',
                DB::raw('COUNT(*) as total_accounts'),
                DB::raw('SUM(CASE WHEN Status IS NOT NULL THEN 1 ELSE 0 END) as reported_count'),
                DB::raw('ROUND(SUM(CASE WHEN Status IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0), 2) as compliance_rate')
            )
            ->groupBy('Company');

        if (!empty($company)) {
            $query->where('Company', $company);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('CreatedAt', [$dateFrom, $dateTo]);
        }

        $summary = $query->get();

        $details = DB::table('cms_cash_record_main')
            ->when(!empty($company), fn($q) => $q->where('Company', $company))
            ->when(!empty($dateFrom) && !empty($dateTo), fn($q) => $q->whereBetween('CreatedAt', [$dateFrom, $dateTo]))
            ->orderBy('CreatedAt', 'desc')
            ->get();

        return response()->json([
            'status'  => 200,
            'summary' => $summary,
            'details' => $details,
        ]);
    }

    public function getTxnDetails(Request $request)
    {
        $cohId = $request->input('coh_id');

        $data = DB::table('cms_cash_record_txn_details as TD')
            ->join('cms_cash_record_main as CM', 'CM.RecID', '=', 'TD.CohID')
            ->where('TD.CohID', $cohId)
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function getDenomination(Request $request)
    {
        $cohId = $request->input('coh_id');

        $data = DB::table('cms_cash_record_denomination')
            ->where('CohID', $cohId)
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }
}
