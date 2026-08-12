<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function tagging()
    {
        return Inertia::render('Branch/BranchTagging');
    }

    public function branchCoh()
    {
        return Inertia::render('Branch/BranchCOH');
    }

    public function branchReport()
    {
        return Inertia::render('Branch/BranchReport');
    }

    public function reconciliation()
    {
        return Inertia::render('Branch/BranchReconciliation');
    }

    public function getBranchList()
    {
        $data = DB::table('tblbranch')->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function getBranchTagging()
    {
        $data = DB::table('cms_branch_tagging')
            ->where('IsDeleted', 0)
            ->orWhereNull('IsDeleted')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function saveBranchTagging(Request $request)
    {
        $trainorId  = $request->input('TrainorID');
        $branchCode = $request->input('BranchCode');

        $existing = DB::table('cms_branch_tagging')
            ->where('TrainorID', $trainorId)
            ->where('BranchCode', $branchCode)
            ->first();

        if ($existing) {
            DB::table('cms_branch_tagging')
                ->where('RecID', $existing->RecID)
                ->update([
                    'IsDeleted' => 0,
                    'UpdatedAt' => now(),
                ]);
        } else {
            DB::table('cms_branch_tagging')->insert([
                'TrainorID'  => $trainorId,
                'BranchCode' => $branchCode,
                'IsDeleted'  => 0,
                'CreatedAt'  => now(),
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Branch tagging saved.',
        ]);
    }

    public function deleteBranchTagging($id)
    {
        DB::table('cms_branch_tagging')
            ->where('RecID', $id)
            ->update(['IsDeleted' => 1]);

        return response()->json([
            'status'  => 200,
            'message' => 'Branch tagging deleted.',
        ]);
    }

    public function getBranchCOH(Request $request)
    {
        $date    = $request->input('date');
        $company = $request->input('company');

        $query = DB::table('cms_cash_record_main as CM')
            ->join('tblbranch as TB', 'TB.BranchCode', '=', 'CM.BranchCode')
            ->select('CM.*', 'TB.BranchName', 'TB.BranchAddress');

        if (!empty($date)) {
            $query->whereDate('CM.CreatedAt', $date);
        }

        if (!empty($company)) {
            $query->where('CM.Company', 'like', "%{$company}%");
        }

        $data = $query->orderBy('CM.CreatedAt', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }
}
