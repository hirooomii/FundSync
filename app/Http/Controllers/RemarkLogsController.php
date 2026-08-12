<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RemarkLogsController extends Controller
{
    public function index()
    {
        return Inertia::render('Logs/RemarkLogs');
    }

    public function getRemarkLogs(Request $request)
    {
        $search = $request->input('search.value', '');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw   = intval($request->input('draw', 1));

        $query = DB::table('cms_note_logs as CU')
            ->leftJoin('users as TU', 'TU.id', '=', 'CU.Maker')
            ->leftJoin('cms_bank_transactions as CS', 'CS.id', '=', 'CU.TransactionID')
            ->select(
                'CU.*',
                'TU.name as EmployeeName',
                'CS.account_no as Account',
                'CS.additional_info as Description'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('CU.Note', 'like', "%{$search}%")
                  ->orWhere('CS.account_no', 'like', "%{$search}%");
            });
        }

        $recordsTotal    = DB::table('cms_note_logs')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy('CU.EditDate', 'desc')
                      ->skip($start)
                      ->take($length)
                      ->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
