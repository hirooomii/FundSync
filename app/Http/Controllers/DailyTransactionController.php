<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DailyTransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('DailyTransaction/DailyTransaction');
    }

    public function fetchJoinedTransactions(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        // Resolve CashAccount code for this account (alphanumeric link e.g. GH00576)
        $cashAccountRecord = DB::table('bank_cash_account')
            ->where('AccountNo', $accountNo)
            ->first();
        $cashAccountCode = $cashAccountRecord?->CashAccount ?? null;

        $query = DB::table('cms_bank_transactions as CBT')
            ->where('CBT.account_no', $accountNo)
            ->orderBy('CBT.transaction_date', 'asc');

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('CBT.transaction_date', [$dateFrom, $dateTo]);
        }

        $bankRows = $query->get();

        // Collect all reference numbers from docref fields
        $refMap = [];
        foreach ($bankRows as $row) {
            if (!empty($row->docref)) {
                preg_match_all('/([^\s,]+)\s*-\s*[\d,.]+/', $row->docref, $matches);
                foreach ($matches[1] as $ref) {
                    $refMap[$ref] = true;
                }
            }
        }

        $acuMap = [];
        if (!empty($refMap)) {
            $refs = array_keys($refMap);
            $acuQuery = DB::table('cms_cashaccount_details')
                ->whereIn('ReferenceNumber', $refs);
            // Filter by the mapped CashAccount code when available
            if ($cashAccountCode) {
                $acuQuery->where('CashAccount', $cashAccountCode);
            }
            $acuRows = $acuQuery->get()->keyBy('ReferenceNumber');
            foreach ($acuRows as $ref => $acu) {
                $acuMap[$ref] = $acu;
            }
        }

        $result = [];
        foreach ($bankRows as $row) {
            $acu = null;
            if (!empty($row->docref)) {
                preg_match('/([^\s,]+)\s*-\s*[\d,.]+/', $row->docref, $m);
                $firstRef = $m[1] ?? null;
                if ($firstRef && isset($acuMap[$firstRef])) {
                    $acu = $acuMap[$firstRef];
                }
            }

            $result[] = [
                // Bank columns
                'id'               => $row->id,
                'bank_code'        => $row->bank_code,
                'account_no'       => $row->account_no,
                'debit_or_credit'  => $row->debit_or_credit,
                'amount'           => $row->amount,
                'transaction_date' => $row->transaction_date,
                'description'      => $row->description,
                'docref'           => $row->docref,
                // Acumatica columns (null if unmatched)
                'acu_account'      => $acu->CashAccount ?? null,
                'acu_reference'    => $acu->ReferenceNumber ?? null,
                'acu_type'         => $acu->Type ?? null,
                'acu_amount'       => $acu->Amount ?? null,
                'acu_date'         => $acu->TransactionDate ?? null,
                'acu_desc'         => $acu->TransactionDesc ?? null,
            ];
        }

        return response()->json($result);
    }

    public function autoBindTransactions(Request $request)
    {
        $accountNo = $request->input('account_no');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        // Resolve the CashAccount code from bank_cash_account mapping table
        // In production this is an alphanumeric code like GH00576 that links to cms_cashaccount_details.CashAccount
        $cashAccountRecord = DB::table('bank_cash_account')
            ->where('AccountNo', $accountNo)
            ->first();

        $cashAccountCode = $cashAccountRecord?->CashAccount ?? null;

        // Build used references to avoid double-binding
        $usedReferences = $this->buildUsedReferences($dateFrom, $dateTo, $accountNo);

        // Fetch unbound bank transactions in the date range
        $query = DB::table('cms_bank_transactions')
            ->where('account_no', $accountNo)
            ->whereNull('docref');

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }

        $transactions = $query->get();

        $bound = 0;
        foreach ($transactions as $txn) {
            $isDebit = in_array(strtoupper($txn->debit_or_credit), ['D', 'DEBIT']);

            // Match cms_cashaccount_details:
            //   bank Debit  → acumatica Credit != 0  (disbursement)
            //   bank Credit → acumatica Debit  != 0  (receipt)
            $matchQuery = DB::table('cms_cashaccount_details')
                ->whereRaw('ABS(Amount) = ?', [abs((float) $txn->amount)]);

            // Filter by CashAccount code if available (the alphanumeric link)
            if ($cashAccountCode) {
                $matchQuery->where('CashAccount', $cashAccountCode);
            }

            // Vice-versa type matching
            if ($isDebit) {
                $matchQuery->where('Credit', '!=', 0); // bank debit ↔ acumatica credit (disbursement)
            } else {
                $matchQuery->where('Debit', '!=', 0);  // bank credit ↔ acumatica debit (receipt)
            }

            if (!empty($usedReferences)) {
                $matchQuery->whereNotIn('ReferenceNumber', $usedReferences);
            }

            $match = $matchQuery->first();

            if ($match) {
                DB::table('cms_bank_transactions')
                    ->where('id', $txn->id)
                    ->update([
                        'docref'        => $match->ReferenceNumber . ' - ' . $txn->amount,
                        'bindAt'        => now(),
                        'reconciledAmt' => $txn->amount,
                        'remainingAmt'  => 0,
                    ]);

                $usedReferences[] = $match->ReferenceNumber;
                $bound++;
            }
        }

        return response()->json(['bound' => $bound, 'message' => "Bound {$bound} transaction(s)."]);
    }

    private function buildUsedReferences(string $dateFrom, string $dateTo, string $accountNo): array
    {
        $rows = DB::table('cms_bank_transactions')
            ->select('docref')
            ->where('account_no', $accountNo)
            ->whereNotNull('docref');

        if (!empty($dateFrom) && !empty($dateTo)) {
            $rows->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        }

        $used = [];
        foreach ($rows->pluck('docref') as $docref) {
            preg_match_all('/([^\s,]+)\s*-\s*[\d,.]+/', $docref, $matches);
            foreach ($matches[1] as $ref) {
                $used[] = trim($ref);
            }
        }
        return array_unique($used);
    }

    public function getReconCompanies()
    {
        $companies = DB::table('cms_bank_depository')
            ->whereNotNull('Company')
            ->where('Company', '!=', '')
            ->distinct()
            ->orderBy('Company')
            ->pluck('Company');

        return response()->json($companies);
    }

    public function getReconBanks(Request $request)
    {
        $company = $request->input('company');

        $query = DB::table('cms_bank_depository')
            ->whereNotNull('BankName')
            ->where('BankName', '!=', '')
            ->distinct()
            ->orderBy('BankName');

        if ($company) {
            $query->where('Company', 'like', "%{$company}%");
        }

        return response()->json($query->pluck('BankName'));
    }

    public function getReconAccounts(Request $request)
    {
        $company  = $request->input('company');
        $bankName = $request->input('bank_name');

        $query = DB::table('cms_bank_depository')
            ->whereNotNull('AccountNo')
            ->where('AccountNo', '!=', '')
            ->orderBy('AccountNo');

        if ($company)  $query->where('Company', 'like', "%{$company}%");
        if ($bankName) $query->where('BankName', 'like', "%{$bankName}%");

        return response()->json($query->get(['AccountNo', 'AccountName']));
    }
}
