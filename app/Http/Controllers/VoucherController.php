<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index()
    {
        return Inertia::render('Voucher/VoucherList');
    }

    public function logs()
    {
        return Inertia::render('Voucher/VoucherLogs');
    }

    public function getVouchers(Request $request)
    {
        $company = $request->input('company');
        $search = $request->input('search', '');

        $query = DB::table('cms_voucher_details')
            ->when($company, fn($q) => $q->where('Company', $company))
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('ReferenceNbr', 'like', "%$search%")
                  ->orWhere('VendorName', 'like', "%$search%")
                  ->orWhere('Purpose', 'like', "%$search%");
            }))
            ->orderBy('RecID', 'desc')
            ->paginate(15);

        return response()->json($query);
    }

    public function getVoucherLogs(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_voucher_logs as VL')
            ->leftJoin('cms_voucher_details as VD', 'VD.ReferenceNbr', '=', 'VL.Reference')
            ->select('VL.*', 'VD.VendorName', 'VD.Total', 'VD.Type')
            ->when($company, fn($q) => $q->where('VL.Company', $company))
            ->orderBy('VL.CreatedAt', 'desc')
            ->paginate(15);

        return response()->json($query);
    }
}
