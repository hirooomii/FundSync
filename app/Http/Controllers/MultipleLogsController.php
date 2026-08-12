<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MultipleLogsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reconcilliation/MultipleLogs');
    }

    public function getMultipleLogs(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_multiple_recon as MR')
            ->leftJoin('users as U', 'U.email', '=', 'MR.CreatedBy')
            ->select('MR.*', 'U.name as CreatedByName');

        if (!empty($company)) {
            $query->where('MR.Company', $company);
        }

        $data = $query->orderBy('MR.CreatedAt', 'desc')->paginate(15);

        return response()->json($data);
    }

    public function previewBankDetails(Request $request)
    {
        $multipleId = $request->input('multiple_id');

        $recon = DB::table('cms_multiple_recon')
            ->where('MultipleID', $multipleId)
            ->first();

        if (!$recon) {
            return response()->json(['data' => []]);
        }

        $data = DB::table('cms_bank_transactions')
            ->where('docref', $recon->BankRef)
            ->get();

        return response()->json(['data' => $data]);
    }

    public function previewBookingDetails(Request $request)
    {
        $multipleId = $request->input('multiple_id');

        $recon = DB::table('cms_multiple_recon')
            ->where('MultipleID', $multipleId)
            ->first();

        if (!$recon) {
            return response()->json(['data' => []]);
        }

        $data = DB::table('cms_cashaccount_details')
            ->where('RefNbr', $recon->AcumaticaRef)
            ->get();

        return response()->json(['data' => $data]);
    }
}
