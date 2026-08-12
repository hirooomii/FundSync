<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionOrderingController extends Controller
{
    public function index()
    {
        return Inertia::render('Transaction/TransactionOrdering');
    }

    public function getGroups(Request $request)
    {
        $data = DB::table('cms_bank_transactions')
            ->whereNotNull('docref')
            ->select(
                'account_no',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('account_no')
            ->orderBy('account_no')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function previewOrder(Request $request)
    {
        $accountNo = $request->input('account_no');

        $data = DB::table('cms_bank_transactions')
            ->where('account_no', $accountNo)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function saveOrder(Request $request)
    {
        $accountNo  = $request->input('account_no');
        $orderedIds = $request->input('ordered_ids', []);

        foreach ($orderedIds as $sortOrder => $id) {
            DB::table('cms_bank_transactions')
                ->where('id', $id)
                ->where('account_no', $accountNo)
                ->update(['sort_order' => $sortOrder + 1]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Order saved successfully.',
        ]);
    }
}
