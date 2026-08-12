<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UnbindLogsController extends Controller
{
    public function index()
    {
        return Inertia::render('Logs/UnbindLogs');
    }

    public function getUnbindLogs(Request $request)
    {
        $search = $request->input('search.value', '');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw   = intval($request->input('draw', 1));

        $query = DB::table('cms_unbind_logs as CU')
            ->leftJoin('users as TU', 'TU.id', '=', 'CU.Maker')
            ->select(
                'CU.*',
                'TU.name as EmployeeName'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('CU.Reference', 'like', "%{$search}%")
                  ->orWhere('CU.TransactionID', 'like', "%{$search}%");
            });
        }

        $recordsTotal    = DB::table('cms_unbind_logs')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy('CU.CreatedAt', 'desc')
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
