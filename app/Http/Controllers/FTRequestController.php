<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FTRequestController extends Controller
{
    public function index()
    {
        return Inertia::render('FundTransfer/FTRequest');
    }

    public function getRequests(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_ft_approval as FTA')
            ->leftJoin('cms_ft_sequence as FTS', 'FTS.FTID', '=', 'FTA.RecID')
            ->select(
                'FTA.*',
                DB::raw('MAX(FTS.Status) as SequenceStatus'),
                DB::raw('COUNT(FTS.RecID) as TotalSignatories'),
                DB::raw('SUM(CASE WHEN FTS.Status = \'APPROVED\' THEN 1 ELSE 0 END) as ApprovedCount')
            )
            ->groupBy('FTA.RecID');

        if (!empty($company)) {
            $query->where('FTA.Company', $company);
        }

        $data = $query->orderBy('FTA.RecID', 'desc')->paginate(15);

        return response()->json($data);
    }
}
