<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ApprovalMatrixController extends Controller
{
    public function index()
    {
        return Inertia::render('ApprovalMatrix/ApprovalMatrix');
    }

    public function getMatrix(Request $request)
    {
        $type = $request->input('type');

        $query = DB::table('cms_approval_matrix')
            ->when($type, fn($q) => $q->where('Type', $type))
            ->orderBy('Type')
            ->orderBy('Sequence')
            ->get();

        return response()->json(['status' => 200, 'data' => $query]);
    }

    public function saveMatrix(Request $request)
    {
        $validated = $request->validate([
            'Signatory'        => 'required|string|max:100',
            'Type'             => 'required|string|max:50',
            'Sequence'         => 'required|integer',
            'PositionSequence' => 'required|integer',
            'IsReturnable'     => 'boolean',
        ]);

        $validated['CreatedAt'] = now();
        $id = DB::table('cms_approval_matrix')->insertGetId($validated);

        return response()->json(['status' => 200, 'id' => $id]);
    }

    public function deleteMatrix($id)
    {
        DB::table('cms_approval_matrix')->where('RecID', $id)->delete();
        return response()->json(['status' => 200]);
    }
}
