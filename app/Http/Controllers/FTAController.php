<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FTAController extends Controller
{
    public function index()
    {
        return Inertia::render('FundTransfer/FTAList');
    }

    public function getVouchers(Request $request)
    {
        $company = $request->input('company');
        $year    = $request->input('year');

        $query = DB::table('cms_voucher_ft_details');

        if (!empty($company)) {
            $query->where('Company', $company);
        }

        if (!empty($year)) {
            $query->whereYear('TransferDate', $year);
        }

        $data = $query->orderBy('RecID', 'desc')->paginate(15);

        return response()->json($data);
    }

    public function getFtaLogs(Request $request)
    {
        $data = DB::table('cms_voucher_logs')
            ->where('Type', 'FUND_TRANSFER')
            ->orderBy('RecID', 'desc')
            ->paginate(15);

        return response()->json($data);
    }
}
