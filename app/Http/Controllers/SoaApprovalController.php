<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SoaApprovalController extends Controller
{
    public function index()
    {
        return Inertia::render('SOA/SoaApproval');
    }

    public function getAllPendingSoas(Request $request)
    {
        $status  = $request->input('status');
        $company = $request->input('company');
        $bank    = $request->input('bank');

        $query = DB::table('cms_statement_of_account as SOA')
            ->join('cms_bank_depository as BD', 'BD.AccountNo', '=', 'SOA.AccountNo')
            ->select('SOA.*', 'BD.BankName', 'BD.Company');

        if (!empty($status)) {
            $query->where('SOA.Status', $status);
        }

        if (!empty($company)) {
            $query->where('BD.Company', $company);
        }

        if (!empty($bank)) {
            $query->where('BD.BankName', 'like', "%{$bank}%");
        }

        $data = $query->orderBy('SOA.RecID', 'desc')->paginate(15);

        return response()->json($data);
    }

    public function approveSoa(Request $request)
    {
        $soaId = $request->input('soa_id');

        DB::table('cms_statement_of_account')
            ->where('RecID', $soaId)
            ->update([
                'Status'     => 'APPROVED',
                'UpdatedAt'  => now(),
            ]);

        return response()->json([
            'status'  => 200,
            'message' => 'SOA approved successfully.',
        ]);
    }

    public function disapproveSoa(Request $request)
    {
        $soaId   = $request->input('soa_id');
        $remarks = $request->input('remarks');

        DB::table('cms_statement_of_account')
            ->where('RecID', $soaId)
            ->update([
                'Status'    => 'DISAPPROVED',
                'Remarks'   => $remarks,
                'UpdatedAt' => now(),
            ]);

        return response()->json([
            'status'  => 200,
            'message' => 'SOA disapproved.',
        ]);
    }

    public function returnSoa(Request $request)
    {
        $soaId   = $request->input('soa_id');
        $remarks = $request->input('remarks');

        DB::table('cms_statement_of_account_returned')->insert([
            'SoaID'     => $soaId,
            'Remarks'   => $remarks,
            'CreatedAt' => now(),
            'CreatedBy' => auth()->id(),
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'SOA returned successfully.',
        ]);
    }

    public function getSoaTransactions(Request $request)
    {
        $soaId = $request->input('soa_id');

        $data = DB::table('cms_bank_transactions_pending')
            ->where('soa_id', $soaId)
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }
}
