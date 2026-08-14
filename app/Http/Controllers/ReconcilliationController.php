<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReconcilliationController extends Controller
{
    public function index()
    {
        return Inertia::render('Reconcilliation/Reconcilliation');
    }

    /**
     * Bank side — cms_bank_transactions filtered by account_no + date range
     */
    public function getBankTransactions(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $query = DB::table('cms_bank_transactions')
            ->where('account_no', $accountNo)
            ->orderBy('transaction_date', 'asc');

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }

        return response()->json($query->get());
    }

    /**
     * Acumatica side — cms_cashaccount_details filtered by the CashAccount code
     * that is linked to the selected bank account via bank_cash_account mapping table.
     */
    public function getAcumaticaBookings(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        // Resolve the CashAccount code (e.g. 100001 / GH00576) from the mapping table
        $cashAccountRecord = DB::table('bank_cash_account')
            ->where('AccountNo', $accountNo)
            ->first();

        $cashAccountCode = $cashAccountRecord?->CashAccount ?? null;

        if (!$cashAccountCode) {
            return response()->json([]);
        }

        $query = DB::table('cms_cashaccount_details')
            ->where('CashAccount', $cashAccountCode)
            ->orderBy('TransactionDate', 'asc');

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('TransactionDate', [$dateFrom, $dateTo]);
        }

        return response()->json($query->get());
    }

    /**
     * Bind a bank transaction to an acumatica booking
     */
    public function bindTransaction(Request $request)
    {
        $bankId    = $request->input('bank_id');
        $acuRecId  = $request->input('acu_rec_id');

        $acu = DB::table('cms_cashaccount_details')->where('RecID', $acuRecId)->first();

        if (!$acu) {
            return response()->json(['message' => 'Acumatica record not found.'], 404);
        }

        DB::table('cms_bank_transactions')
            ->where('id', $bankId)
            ->update([
                'docref'        => $acu->ReferenceNumber . ' - ' . $acu->Amount,
                'bindAt'        => now(),
                'reconciledAmt' => $acu->Amount,
                'remainingAmt'  => 0,
            ]);

        return response()->json(['message' => 'Bound successfully.']);
    }
}
