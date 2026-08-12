<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnswerApprovalController extends Controller
{
    public function index()
    {
        return Inertia::render('FundTransfer/AnswerApproval');
    }

    public function getApprovalQueue(Request $request)
    {
        $userEmail = auth()->user()->email;

        $data = DB::table('cms_ft_approval as FTA')
            ->join('cms_ft_sequence as FTS', function ($join) {
                $join->on('FTS.FTID', '=', 'FTA.RecID')
                     ->where('FTS.Status', '!=', 'APPROVED');
            })
            ->where('FTS.Signatory', $userEmail)
            ->select('FTA.*', 'FTS.RecID as SequenceID', 'FTS.Status as SequenceStatus', 'FTS.Signatory')
            ->orderBy('FTA.RecID', 'desc')
            ->paginate(15);

        return response()->json($data);
    }

    public function approveRequest(Request $request)
    {
        $ftid       = $request->input('ftid');
        $sequenceId = $request->input('sequence_id');

        DB::table('cms_ft_sequence')
            ->where('RecID', $sequenceId)
            ->update([
                'Status'       => 'APPROVED',
                'ApprovedDate' => now(),
            ]);

        $total    = DB::table('cms_ft_sequence')->where('FTID', $ftid)->count();
        $approved = DB::table('cms_ft_sequence')->where('FTID', $ftid)->where('Status', 'APPROVED')->count();

        if ($total > 0 && $total === $approved) {
            DB::table('cms_ft_approval')
                ->where('RecID', $ftid)
                ->update(['IsFullyApproved' => 1]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Request approved.',
        ]);
    }

    public function disapproveRequest(Request $request)
    {
        $ftid   = $request->input('ftid');
        $reason = $request->input('reason');

        DB::table('cms_ft_approval')
            ->where('RecID', $ftid)
            ->update(['IsDisapproved' => 1]);

        DB::table('cms_ft_disapproved')->insert([
            'FTID'      => $ftid,
            'Reason'    => $reason,
            'CreatedAt' => now(),
            'CreatedBy' => auth()->user()->email,
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Request disapproved.',
        ]);
    }
}
