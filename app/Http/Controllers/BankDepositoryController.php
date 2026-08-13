<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BankDepositoryModel;
use App\Models\ListOfBankModel;
use App\Models\AccountTypeModel;
use App\Models\AccountTagModel;
use App\Models\BankSignatories;
use App\Models\CashAccountModel;
use App\Models\SOAModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Models\CmsBankBalance;
use App\Models\CmsBankBalancePending;
use App\Models\CmsBankTransaction;
use App\Models\CmsBankTransactionPending;
use App\Models\UserPositionModel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BankDepositoryController extends Controller
{
    public function index() 
    {
        $depository = BankDepositoryModel::all();
        $banks = ListOfBankModel::where('Status', 1)->get();
        $accounttype = AccountTypeModel::where('Status', 1)->get();
        $accounttag = AccountTagModel::where('Status', 1)->get();
        $company = CompanyModel::where('Status', 1)->get();
        return Inertia::render('Depository/DepositoryBank', [
            'depository' => $depository,
            'banks' => $banks,
            'accountType' => $accounttype,
            'accountTag' => $accounttag,
            'company' => $company,
        ]);
    }

    public function retrieveBankDepository() 
    {
        $depository = BankDepositoryModel::all();
        return response()->json($depository);
    }

    public function retrieveAccountDetails($accountNo) 
    {
        $AccountDetails = BankDepositoryModel::where('AccountNo', $accountNo)->first();

        if (!$AccountDetails) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return response()->json($AccountDetails);
    }

    
    public function bankSignatories($accountNo)
    {
        $accounts = BankSignatories::with('position', 'user')
            ->where('AccountNo', $accountNo)
            ->get();

        return response()->json($accounts);
    }

    public function retrieveAccountSOA($accountNo) 
    {
        $soa = SOAModel::with('transactBy', 'user')
            ->where('AccountNo', $accountNo)
            ->get();

        return response()->json($soa);
    }

    public function accountCashAccount($accountNo)
    {
        $cashaccount = CashAccountModel::with('user')
            ->where('AccountNo', $accountNo)
            ->get();

        return response()->json($cashaccount);
    }

    public function retrieveAccountTransaction($accountNo) {

        $transaction = CmsBankTransaction::with('user')
            ->where('account_no', $accountNo)
            ->get();

        return response()->json($transaction);
    }

    public function insertSignatory(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required|exists:users,id',
            'account' => 'required|exists:cms_bank_depository,AccountNo',
        ]);

        $user = UserModel::find($validated['id']);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $data = [
            'EmployeeID' => $user->id ?? null,  
            'Name'       => $user->FullName ?? ($user->name ?? ''), 
            'Position'   => $user->position ?? '',      
            'AccountNo'  => $validated['account'],
            'CreatedBy'  => Auth::id(),
            'CreatedAt'  => now(),
            'Status'     => 1,
        ];

        $inserted = BankSignatories::create($data);

        return response()->json([
            'message' => 'Signatory successfully added.',
            'data'    => $inserted
        ]);

    }

    public function updateDepositoryBank(Request $request) 
    {
        $validated = $request->validate([
            'RecID' => 'required|exists:cms_bank_depository,RecID',
            'Company' => 'required',
            'BankName' => 'required',
            'AccountNo' => 'required',
            'AccountName' => 'required',
            'DepositType' => 'required',
            'Description' => 'required',
            'AccountTag' => 'required',
            'InterestRate' => 'nullable|numeric',
            'BeginningBal' => 'nullable|numeric',
            'MaintainingBal' => 'nullable|numeric',
            'BankBranch' => 'required',
            'BankStreet' => 'required',
            'BankCity' => 'required',
            'BankProvince' => 'required',
            'DateOpen' => 'nullable|date',
            'MaturityDate' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if (strtolower($request->DepositType) === 'time deposit' && empty($value)) {
                        $fail('The Maturity Date field is required for Time Deposit accounts.');
                    }
                },
            ],
            'DepContactNum' => 'required',
            'DepContactPerson' => 'required',
            'DepEmailAdd' => 'required|email',
            'DepPosition' => 'required',
        ]);

        $bank = BankDepositoryModel::find($validated['RecID']);

        if (!$bank) {
            return response()->json([
                'success' => false,
                'message' => 'Bank record not found.'
            ], 404);
        }

        $validated['CreatedBy'] = Auth::id();
        $validated['CreatedAt'] = now();

        $bank->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bank Depository updated successfully.',
            'data' => $bank,
        ]);
    }

    public function saveDepositoryBank(Request $request)
    {
        $validated = $request->validate([
            'Company' => 'required',
            'BankName' => 'required',
            'AccountNo' => 'required',
            'AccountName' => 'required',
            'DepositType' => 'required',
            'Description' => 'required',
            'AccountTag' => 'required',
            'InterestRate' => 'nullable|numeric',
            'BeginningBal' => 'nullable|numeric',
            'MaintainingBal' => 'nullable|numeric',
            'BankBranch' => 'required',
            'BankStreet' => 'required',
            'BankCity' => 'required',
            'BankProvince' => 'required',
            'DateOpen' => 'nullable|date',
            'MaturityDate' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if (strtolower($request->DepositType) === 'time deposit' && empty($value)) {
                        $fail('The Maturity Date field is required for Time Deposit accounts.');
                    }
                },
            ],
            'DepContactNum' => 'required',
            'DepContactPerson' => 'required',
            'DepEmailAdd' => 'required|email',
            'DepPosition' => 'required',
        ]);

        $validated['CreatedBy'] = Auth::id();          
        $validated['Status'] = 'ACTIVE';                 
        $validated['CreatedAt'] = Carbon::now();      

        $newDepository = BankDepositoryModel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bank Depository added successfully.',
            'data' => $newDepository,
        ]);
    }

    public function updateBankStatus(Request $request) 
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|string',
        ]);

        $recID = $request->id;
        $status = $request->status;

        $bankAcoount = BankDepositoryModel::where('RecID', $recID)->first();
        if (!$bankAcoount) {
            return response()->json(['message' => 'Cash Account not found.'], 404);
        }

        $bankAcoount->Status = $status;
        $bankAcoount->save();

        $message = $request->Status == 'ACTIVE' 
        ? 'Account successfully Activated!' 
        : 'Account successfully Deactivated!';

        return response()->json(['message' => $message]);
    }

    public function updateSignatoryStatus(Request $request) 
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        $recID = $request->id;
        $status = $request->status;

        
        $bankSignatory = BankSignatories::where('RecID', $recID)->first();
        if (!$bankSignatory) {
            return response()->json(['message' => 'Bank Signatories not found.'], 404);
        }

        $bankSignatory->Status = $status;
        $bankSignatory->save();

        $message = $request->Status == '1' 
        ? 'Signatory successfully Activated!' 
        : 'Signatory successfully Deactivated!';

        return response()->json(['message' => $message]);
    }

    public function insertStatementofAccount(Request $request)
    {
        try {
            $validated = $request->validate([
                'accountno' => 'required|string|max:550',
                'transactionat' => 'nullable|date',
                'passbookbal' => 'nullable|numeric',
                'remarks' => 'nullable|string|max:550',
                'base64' => 'nullable|string',
                'extensionFile' => 'nullable|string|max:10',
            ]);

            $filePath = null;

            if (!empty($validated['base64']) && !empty($validated['extensionFile'])) {
                $fileName = 'SOA_' . time() . '.' . $validated['extensionFile'];
                $relativePath = 'uploads/soa/' . $fileName;

                Storage::disk('public')->put($relativePath, base64_decode($validated['base64']));

                $filePath = $relativePath;
            }

            $soa = SOAModel::create([
                'SOA'         => $filePath, 
                'AccountNo'   => $validated['accountno'],
                'PassbookBal' => $validated['passbookbal'],
                'Remarks'     => $validated['remarks'],
                'TransacBy'   => Auth::id(),   
                'TransacAt'   => $validated['transactionat'] ?? Carbon::now(),
                'CreatedBy'   => Auth::id(),     
                'CreatedAt'   => Carbon::now(),
                'Status'      => 'PENDING',
                'IsExcel'     => 0,
                'Attachment'  => 0,
            ]);

           return response()->json([
                'message' => 'SOA record created successfully.',
                'SOAID' => $soa->RecID, 
                'data' => $soa
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to insert SOA record.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function insertBalanceTransactions(Request $request)
    {
        try {
            $balances = $request->input('balances');
            $transactions = $request->input('transactions', []);
            $SOAID = $request->input('SOAID', 0);

            if (!$balances) {
                return response()->json(['status' => 'error', 'message' => 'Missing balances data'], 400);
            }

                // Always use the SOA record's AccountNo — the selected account wins over whatever the PDF parser extracted
            $soa = SOAModel::find($SOAID);
            $accountNo = $soa?->AccountNo ?? ($balances['account_no'] ?? null);

            $balances['account_no'] = $accountNo;
            $balances['SOAID'] = $SOAID;
            if (!empty($balances['transaction_date'])) {
                $balances['transaction_date'] = $this->normalizeDate($balances['transaction_date']);
            }

            if (empty($transactions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No transactions were extracted from the PDF. Please verify the file format matches the selected bank.',
                ], 422);
            }

            // Whitelist of allowed columns to prevent unknown-column DB errors
            $allowedTxFields = [
                'account_no', 'bank_code', 'transaction_date', 'amount', 'runningbal',
                'debit_or_credit', 'transaction_type', 'description', 'reference',
                'additional_info', 'SOAID',
            ];

            foreach ($transactions as &$transaction) {
                $transaction['account_no'] = $accountNo;
                $transaction['SOAID'] = $SOAID;
                if (!empty($transaction['transaction_date'])) {
                    $transaction['transaction_date'] = $this->normalizeDate($transaction['transaction_date']);
                }
                $transaction = array_intersect_key($transaction, array_flip($allowedTxFields));
            }
            unset($transaction);

            DB::beginTransaction();

            DB::table('cms_bank_balances_pending')->insert($balances);

            if (!empty($transactions)) {
                foreach (array_chunk($transactions, 50) as $chunk) {
                    DB::table('cms_bank_transactions_pending')->insert($chunk);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'All data imported successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Insert failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function normalizeDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) return null;

        // Already YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) return $dateStr;

        $formats = ['m/d/Y', 'Y/m/d', 'd/m/Y', 'd-M-Y', 'M d, Y', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateStr);
                if ($date && $date->format($format) === $dateStr) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {}
        }

        // Last resort
        $timestamp = strtotime($dateStr);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    public function approveSOA(Request $request)
    {
        $id = $request->input('id');
        $userId = Auth::id();
        $date = Carbon::now();

        DB::beginTransaction();

        try {
            $balancePending = CmsBankBalancePending::where('SOAID', $id)->first();

            if ($balancePending) {
                $exists = CmsBankBalance::where('account_no', $balancePending->account_no)
                    ->where('transaction_date', $balancePending->transaction_date)
                    ->exists();

                if (!$exists) {
                    $data = $balancePending->toArray();
                    unset($data['id']);
                    CmsBankBalance::create($data);
                }
            }

            $pendingTx = CmsBankTransactionPending::where('SOAID', $id)
                ->orderBy('id', 'asc')
                ->get();

            $validTx = [];

            foreach ($pendingTx as $tx) {
                $exists = CmsBankTransaction::where('account_no', $tx->account_no)
                    ->where('transaction_date', $tx->transaction_date)
                    ->where('amount', $tx->amount)
                    ->where('runningbal', $tx->runningbal)
                    ->where('debit_or_credit', $tx->debit_or_credit)
                    ->where('transaction_type', $tx->transaction_type)
                    ->where('description', $tx->description)
                    ->where('reference', $tx->reference)
                    ->where('additional_info', $tx->additional_info)
                    ->exists();

                if (!$exists) {
                    $data = $tx->toArray();
                    unset($data['id']);
                    $validTx[] = $data;
                }
            }

            if (!empty($validTx)) {
                CmsBankTransaction::insert($validTx);
            }

            CmsBankTransactionPending::where('SOAID', $id)->delete();
            CmsBankBalancePending::where('SOAID', $id)->delete();

            SOAModel::where('RecID', $id)->update([
                'Status' => 'APPROVED',
                'Approver' => $userId,
                'Approved' => $date,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'SOA Successfully Approved'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function disapproveSOA(Request $request)
    {
        try {
            $id = $request->input('id');
            $userId = Auth::id();
            $date = now();

            $soa = SOAModel::findOrFail($id);
            $soa->update([
                'Status' => 'DISAPPROVED',
                'Approver' => $userId,
                'Approved' => $date,
            ]);

            CmsBankTransactionPending::where('SOAID', $id)->delete();
            CmsBankBalancePending::where('SOAID', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'SOA successfully disapproved.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Disapproval failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importTransaction(Request $request) {

        $validated = $request->validate([
            'excelbase64' => 'required|string',
            'excelext' => 'required|string',
            'AccountNo' => 'required|string',
            'Bank' => 'required|string',
            'Remarks' => 'nullable|string',
            'PassbookBal' => 'nullable|numeric',
            'transactions' => 'array'
        ]);


        DB::beginTransaction();
        try {

            $filePath = $this->storeExcelFile(
                $validated['AccountNo'],
                $validated['excelbase64'],
                $validated['excelext']
            );
            
            $soa = new SOAModel();
            $soa->SOA = $filePath;
            $soa->AccountNo = $validated['AccountNo'];
            $soa->Remarks = $validated['Remarks'] ?? '';
            $soa->PassbookBal = $validated['PassbookBal'] ?? 0;
            $soa->IsExcel = 1;
            $soa->Status = 'PENDING';
            $soa->CreatedBy = Auth::id();
            $soa->CreatedAt = now();
            $soa->TransacAt = now();
            $soa->TransacBy = Auth::id();

            $soa->save();

            $excelData = $validated['transactions'];
            $opening_balance = null;
            $closing_balance = null;
            $last_transaction_date = null;
            $transactionInserts = [];

            foreach ($excelData as $index => $row) {
                $rawDate = trim($row['DATE'] ?? '');
                $formattedDate = null;

                if ($rawDate) {
                    try {
                        $formattedDate = \Carbon\Carbon::createFromFormat('m/d/Y', $rawDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        throw new \Exception("Invalid date format on row " . ($index + 1) . ": $rawDate");
                    }
                }

                $data = [
                    'account_no'       => $validated['AccountNo'],
                    'bank_code'        => $validated['Bank'],
                    'transaction_date' => $formattedDate,
                    'amount'           => $row['AMOUNT'] ?? 0,
                    'debit_or_credit'  => $row['DEBIT/CREDIT'] ?? null,
                    'transaction_type' => $row['TRANSACTION TYPE'] ?? null,
                    'description'      => $row['DESCRIPTION'] ?? null,
                    'reference'        => $row['REFERENCE'] ?? null,
                    'additional_info'  => $row['ADDITIONAL INFO'] ?? null,
                    'runningbal'       => $row['RUNNING BALANCE'] ?? 0,
                    'SOAID'            => $soa->RecID,
                ];

                if (empty($data['account_no']) || empty($data['amount']) || empty($data['transaction_date'])) {
                    throw new \Exception("Row " . ($index + 1) . " is missing required fields.");
                }

                if ($index === 0) {
                    $opening_balance = $data['runningbal'];
                }

                $closing_balance = $data['runningbal'];
                $last_transaction_date = $formattedDate;

                $transactionInserts[] = $data;
            }

            CmsBankTransactionPending::insert($transactionInserts);

            $bankBalance = [
                'account_no'        => $validated['AccountNo'],
                'currency'          => 'PHP',
                'opening_balance'   => $opening_balance,
                'closing_balance'   => $closing_balance,
                'available_balance' => $closing_balance,
                'transaction_date'  => $last_transaction_date,
                'SOAID'             => $soa->RecID,
            ];

            CmsBankBalancePending::create($bankBalance);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'All data imported successfully',
                'SOAID' => $soa->RecID,
                'bankBalance' => $bankBalance,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function storeExcelFile($accountNo, $base64, $ext)
    {
        $fileName = 'SOA_' . $accountNo . '_' . time() . '.' . $ext;
        $relativePath = 'uploads/soa/' . $fileName;

        $decodedFile = base64_decode($base64);
        Storage::disk('public')->put($relativePath, $decodedFile);

        return $relativePath; 
    }

}
