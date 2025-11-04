<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CashAccountModel;
use App\Models\BankDepositoryModel;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CashAccountController extends Controller
{

    private $username;
    private $password;
    private $companies;

    public function __construct()
    {
        $this->username = env('TREASURY_USERNAME');
        $this->password = env('TREASURY_PASSWORD');
        $this->companies = json_decode(env('TREASURY_COMPANIES'), true);
    }

    public function index() {
        $accounts = CashAccountModel::all();
        $depository = BankDepositoryModel::all();
        return Inertia::render('CashAccount/CashAccount', [
            'accounts'   => $accounts,
            'depository' => $depository
        ]);
    }

    public function retrieveCashAccount()
    {
        $accounts = CashAccountModel::all();
        return response()->json($accounts);
    }

    public function bindAccount(Request $request)
    {
        $request->validate([
            'cashAccount' => 'required|string',
            'bindAccountNo' => 'required|string|exists:cms_bank_depository,AccountNo',
        ]);

        $cashAccountNo = $request->cashAccount;
        $bindAccountNo = $request->bindAccountNo;

        $cashAccount = CashAccountModel::where('CashAccount', $cashAccountNo)->first();
        if (!$cashAccount) {
            return response()->json(['message' => 'Cash Account not found.'], 404);
        }

        $cashAccount->AccountNo = $bindAccountNo;
        $cashAccount->save();

        return response()->json(['message' => 'Account successfully bound!']);
    }

    public function accountStatus(Request $request) 
    {
        $request->validate([
            'cashAccount' => 'required|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $cashaccount = $request->cashAccount;
        $status = $request->status;

        $cashAccount = CashAccountModel::where('CashAccount', $cashaccount)->first();
        if (!$cashAccount) {
            return response()->json(['message' => 'Cash Account not found.'], 404);
        }

        $cashAccount->IsActive = $status;
        $cashAccount->save();

        $message = $request->status == 1 
        ? 'Account successfully Activated!' 
        : 'Account successfully Deactivated!';

        return response()->json(['message' => $message]);
    }

    public function mergeCashAccount()
    {
        $depositories = BankDepositoryModel::all();
        $totalUpdates = 0;

        foreach ($depositories as $depo) {
            $original = $depo->AccountNo;
            $cleaned  = ltrim($original, '0'); 

            $affected = DB::table('bank_cash_account')
                ->where(function ($query) use ($original, $cleaned) {
                    $query->whereRaw("REPLACE(REPLACE(Description, '-', ''), ' ', '') LIKE ?", ['%' . $cleaned . '%'])
                        ->orWhereRaw("REPLACE(REPLACE(Description, '-', ''), ' ', '') LIKE ?", ['%' . $original . '%']);
                })
                ->update(['AccountNo' => $original]);

            $totalUpdates += $affected;
        }

        return response()->json([
            'update' => "Total Cash Accounts Merged: {$totalUpdates}"
        ]);
    }

    public function fetchCashAccounts()
    {
        $insertCount = 0;
        $updateCount = 0;

        try {
            foreach ($this->companies as $company => $url) {
                $response = Http::withoutVerifying()
                    ->withBasicAuth($this->username, $this->password)
                    ->get($url, ['$format' => 'json']);

                if ($response->failed()) {
                    continue; 
                }

                $data = $response->json();

                foreach ($data['value'] ?? [] as $item) {
                    $isActive = $item['Active'] ? '1' : '0';
                    $branchName = str_replace('RC ', '', $item['BranchName']);

                    $cashAccountData = [
                        'Company' => $company,
                        'Branch' => trim($branchName),
                        'BranchID' => trim($item['Branch']),
                        'CashAccount' => trim($item['CashAccount']),
                        'Description' => trim($item['Description']),
                        'IsActive' => $isActive,
                    ];

                    $existing = CashAccountModel::where('CashAccount', $cashAccountData['CashAccount'])
                        ->where('Company', $company)
                        ->first();

                    if ($existing) {
                        $existing->update($cashAccountData);
                        $updateCount++;
                    } else {
                        CashAccountModel::create($cashAccountData);
                        $insertCount++;
                    }
                }
            }

            return response()->json([
                'update' => "Inserted: $insertCount, Updated: $updateCount"
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

}
