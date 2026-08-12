<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UnpostedBookController extends Controller
{
    public function index()
    {
        return Inertia::render('UnpostedBook/UnpostedBook');
    }

    public function getUnpostedEntries(Request $request)
    {
        $company    = $request->input('company');
        $bankName   = $request->input('bank_name');
        $accountNo  = $request->input('account_no');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');

        $query = DB::table('cms_cashaccount_unposted');

        if (!empty($company)) {
            $query->where('Company', $company);
        }

        if (!empty($bankName)) {
            $query->where('BankName', 'like', "%{$bankName}%");
        }

        if (!empty($accountNo)) {
            $query->where('AccountNo', $accountNo);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('TransactionDate', [$dateFrom, $dateTo]);
        }

        $data = $query->orderBy('RecID', 'desc')->paginate(15);

        return response()->json($data);
    }

    public function saveNote(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $note          = $request->input('note');

        DB::table('cms_note_logs')->insert([
            'Maker'         => auth()->id(),
            'Note'          => $note,
            'TransactionID' => $transactionId,
            'EditDate'      => now(),
        ]);

        DB::table('cms_cashaccount_unposted')
            ->where('RecID', $transactionId)
            ->update(['Note' => $note]);

        return response()->json([
            'status'  => 200,
            'message' => 'Note saved successfully.',
        ]);
    }

    public function getComments(Request $request)
    {
        $transactionId = $request->input('transaction_id');

        $data = DB::table('cms_note_logs')
            ->where('TransactionID', $transactionId)
            ->orderBy('EditDate', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }
}
