<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MultipleReconController extends Controller
{
    public function index()
    {
        return Inertia::render('Reconcilliation/MultipleApproval');
    }

    public function getReconGroups(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_multiple_recon')
            ->select(
                'MultipleID',
                'Company',
                DB::raw('SUM(Amount) as TotalAmount'),
                DB::raw('COUNT(*) as EntryCount'),
                DB::raw('MIN(Status) as Status'),
                DB::raw('MAX(CreatedAt) as CreatedAt'),
                'CreatedBy'
            )
            ->when($company, fn($q) => $q->where('Company', $company))
            ->groupBy('MultipleID', 'Company', 'CreatedBy')
            ->orderBy('CreatedAt', 'desc')
            ->paginate(15);

        return response()->json($query);
    }

    public function getReconDetails($multipleId)
    {
        $entries = DB::table('cms_multiple_recon')
            ->where('MultipleID', $multipleId)
            ->get();

        return response()->json(['status' => 200, 'data' => $entries]);
    }

    public function approveRecon(Request $request)
    {
        $request->validate(['multiple_id' => 'required|string']);

        DB::table('cms_multiple_recon')
            ->where('MultipleID', $request->multiple_id)
            ->update([
                'Status'     => 1,
                'Approver'   => auth()->user()->email ?? 'system',
                'ApprovedAt' => now(),
            ]);

        return response()->json(['status' => 200]);
    }

    public function disapproveRecon(Request $request)
    {
        $request->validate(['multiple_id' => 'required|string', 'remarks' => 'nullable|string']);

        DB::table('cms_multiple_recon')
            ->where('MultipleID', $request->multiple_id)
            ->update([
                'Status'     => 0,
                'Remarks'    => $request->remarks,
                'Approver'   => auth()->user()->email ?? 'system',
                'ApprovedAt' => now(),
            ]);

        return response()->json(['status' => 200]);
    }
}
