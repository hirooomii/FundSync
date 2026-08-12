<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AcumaticaPassbookController extends Controller
{
    public function index()
    {
        return Inertia::render('AcumaticaPassbook/AcumaticaPassbook');
    }

    public function getEntries(Request $request)
    {
        $company   = $request->input('company');
        $bankName  = $request->input('bank_name');
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = DB::table('cms_cashaccount_details as CD')
            ->leftJoin('bank_cash_account as BCA', 'BCA.CashAccount', '=', 'CD.CashAccount')
            ->select('CD.*', 'BCA.BankName', 'BCA.Company');

        if (!empty($company)) {
            $query->where('BCA.Company', 'like', "%{$company}%");
        }

        if (!empty($bankName)) {
            $query->where('BCA.BankName', 'like', "%{$bankName}%");
        }

        if (!empty($accountNo)) {
            $query->where('CD.CashAccount', $accountNo);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('CD.TransactionDate', [$dateFrom, $dateTo]);
        }

        $data = $query->orderBy('CD.TransactionDate', 'desc')->paginate(15);

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

        DB::table('cms_cashaccount_details')
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
