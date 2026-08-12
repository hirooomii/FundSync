<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FundTransferController extends Controller
{
    public function index()
    {
        return Inertia::render('FundTransfer/FundTransferList');
    }

    public function getFundTransfers(Request $request)
    {
        $company = $request->input('company');
        $search  = $request->input('search', '');

        $query = DB::table('cms_ft_approval as FTA')
            ->leftJoin('cms_ft_sequence as FTS', 'FTS.FTID', '=', 'FTA.RecID')
            ->select(
                'FTA.RecID', 'FTA.Serial', 'FTA.Reference', 'FTA.Type',
                'FTA.Company', 'FTA.CreatedAt', 'FTA.CreatedBy',
                'FTA.IsDisapproved', 'FTA.IsFullyApproved',
                DB::raw('COUNT(DISTINCT FTS.RecID) as total_approvers'),
                DB::raw("SUM(CASE WHEN FTS.Status = 'APPROVED' THEN 1 ELSE 0 END) as approved_count")
            )
            ->when($company, fn($q) => $q->where('FTA.Company', $company))
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('FTA.Serial', 'like', "%$search%")
                  ->orWhere('FTA.Reference', 'like', "%$search%");
            }))
            ->groupBy(
                'FTA.RecID', 'FTA.Serial', 'FTA.Reference', 'FTA.Type',
                'FTA.Company', 'FTA.CreatedAt', 'FTA.CreatedBy',
                'FTA.IsDisapproved', 'FTA.IsFullyApproved'
            )
            ->orderBy('FTA.RecID', 'desc')
            ->paginate(15);

        return response()->json($query);
    }

    public function getFundTransferDetails($id)
    {
        $header   = DB::table('cms_ft_approval')->where('RecID', $id)->first();
        $sequence = DB::table('cms_ft_sequence')->where('FTID', $id)->orderBy('Sequence')->get();
        $voucher  = DB::table('cms_fund_voucher')->where('TransferNbr', $header->Serial ?? '')->get();

        return response()->json([
            'status'   => 200,
            'header'   => $header,
            'sequence' => $sequence,
            'voucher'  => $voucher,
        ]);
    }

    public function approveFundTransfer(Request $request)
    {
        $request->validate(['ftid' => 'required|integer', 'sequence_id' => 'required|integer']);

        DB::table('cms_ft_sequence')
            ->where('RecID', $request->sequence_id)
            ->update(['Status' => 'APPROVED', 'ApprovedDate' => now()]);

        $remaining = DB::table('cms_ft_sequence')
            ->where('FTID', $request->ftid)
            ->where('Status', '!=', 'APPROVED')
            ->count();

        if ($remaining === 0) {
            DB::table('cms_ft_approval')->where('RecID', $request->ftid)->update(['IsFullyApproved' => 1]);
        }

        return response()->json(['status' => 200]);
    }
}
