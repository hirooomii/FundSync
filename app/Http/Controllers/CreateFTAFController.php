<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CreateFTAFController extends Controller
{
    public function index()
    {
        return Inertia::render('FundTransfer/CreateFTAF');
    }

    public function getList(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_rtof_list');

        if (!empty($company)) {
            $query->where('Company', $company);
        }

        $data = $query->orderBy('RecID', 'desc')->paginate(15);

        return response()->json($data);
    }
}
