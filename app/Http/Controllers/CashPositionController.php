<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class CashPositionController extends Controller
{
    public function index()
    {
        return Inertia::render('CashPosition/CashPosition');
    }

    public function getCashPositions(Request $request)
    {
        $company  = $request->input('company');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $query = DB::table('cms_cash_position')
            ->when($company, fn($q) => $q->where('Company', $company))
            ->whereBetween('RefDate', [$dateFrom, $dateTo])
            ->orderBy('RefDate', 'desc')
            ->get();

        return response()->json(['status' => 200, 'data' => $query]);
    }

    public function saveCashPosition(Request $request)
    {
        $validated = $request->validate([
            'RefDate'       => 'required|date',
            'CashAccount'   => 'required|string',
            'CashAvailable' => 'required|numeric',
            'CashForRepo'   => 'nullable|numeric',
            'Company'       => 'required|string',
        ]);

        $validated['TotalCash']  = ($validated['CashAvailable'] ?? 0) + ($validated['CashForRepo'] ?? 0);
        $validated['CreatedBy']  = auth()->user()->email ?? 'system';
        $validated['CreatedAt']  = now();

        $id = DB::table('cms_cash_position')->insertGetId($validated);

        return response()->json(['status' => 200, 'id' => $id]);
    }
}
