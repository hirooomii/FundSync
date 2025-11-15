<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private string $baseUri;
    private string $appKey;
    private string $clientId;
    private string $secretKey;
    private string $acumaticaBaseUrl;
    private string $acumaticaUsername;
    private string $acumaticaPassword;

    public function __construct()
    {
        $this->baseUri = env('TREASURY_BASE_URI');
        $this->appKey = env('TREASURY_APP_KEY');
        $this->clientId = env('TREASURY_CLIENT_ID');
        $this->secretKey = env('TREASURY_SECRET_KEY');
        $this->acumaticaBaseUrl = env('ACUMATICA_BASE_URL');
        $this->acumaticaUsername = env('ACUMATICA_USERNAME');
        $this->acumaticaPassword = env('ACUMATICA_PASSWORD');
    }

    public function index()
    {
        return Inertia::render('Dashboard/Dashboard');
    }

    private function getToken(): string
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'accept' => 'application/json',
                'Client-ID' => $this->clientId,
                'Secret-Key' => $this->secretKey,
                'content-type' => 'application/json',
            ])
            ->timeout(100)
            ->post("{$this->baseUri}/ApiCMS/FetchRequest", [
                'appKey' => $this->appKey,
                'action' => 'GET_TOKEN'
            ]);

        return $response->json('data.token');
    }

    private function makeTreasuryRequest(string $action, array $data = [])
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getToken(),
                'content-type' => 'application/json',
            ])
            ->timeout(100)
            ->post("{$this->baseUri}/ApiCMS/FetchRequest", [
                'appKey' => $this->appKey,
                'action' => $action,
                'data' => $data
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        return response()->json(['error' => 'Unexpected response status'], 500);
    }

    public function getBankAccountSummaries()
    {
        $query = DB::select("
            SELECT 
                CB.account_no AS account_number,
                SUM(CASE WHEN CT.debit_or_credit LIKE '%C%' THEN CT.amount ELSE 0 END) AS total_withdrawal,
                SUM(CASE WHEN CT.debit_or_credit LIKE '%D%' THEN CT.amount ELSE 0 END) AS total_deposit,
                CB.available_balance
            FROM cms_bank_balances CB
            LEFT JOIN cms_bank_transactions CT ON CT.account_no = CB.account_no
            LEFT JOIN cms_bank_balances CB2 
                ON CB.account_no = CB2.account_no 
                AND CB.transaction_date < CB2.transaction_date
            WHERE CB2.account_no IS NULL
            GROUP BY CB.account_no, CB.available_balance
        ");

        return response()->json([
            'status' => 200,
            'data' => $query
        ]);
    }

    public function companyTotalBalance()
    {
        $query = DB::select("
            SELECT 
                CBD.COMPANY AS depository_company, 
                SUM(LB.available_balance) AS totalBalance
            FROM cms_bank_depository AS CBD
            LEFT JOIN (
                SELECT account_no, available_balance
                FROM (
                    SELECT 
                        account_no,
                        available_balance,
                        ROW_NUMBER() OVER (PARTITION BY account_no ORDER BY transaction_date DESC) AS rn
                    FROM cms_bank_balances
                ) AS ranked
                WHERE rn = 1
            ) AS LB ON CBD.AccountNo = LB.account_no
            GROUP BY CBD.COMPANY
        ");

        return response()->json([
            'status' => 200,
            'data' => $query
        ]);
    }

    public function bankBalances()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        
        $query = DB::table('cms_bank_balances as CB')
            ->join('cms_bank_depository as CD', 'CD.AccountNo', '=', 'CB.account_no')
            ->select('CD.Company', DB::raw('SUM(CB.available_balance) as total_balance'), 'CB.transaction_date')
            ->whereBetween('CB.transaction_date', [$thirtyDaysAgo, $today])
            ->groupBy('CD.Company', 'CB.transaction_date')
            ->get();

        if ($query->isEmpty()) {
            $query = DB::table('cms_bank_balances as CB')
                ->join('cms_bank_depository as CD', 'CD.AccountNo', '=', 'CB.account_no')
                ->select('CD.Company', DB::raw('SUM(CB.available_balance) as total_balance'), 'CB.transaction_date')
                ->groupBy('CD.Company', 'CB.transaction_date')
                ->orderBy('CB.transaction_date', 'asc')
                ->get();
        }

        return response()->json([
            'status' => 200,
            'data' => $query,
            'message' => $query->isEmpty() ? 'No data available' : 'Data retrieved successfully'
        ]);
    }

    public function reconStatus()
    {
        $query = DB::select("
            SELECT
                BD.BankName,
                SUM(
                    CASE 
                        WHEN T.comment IS NULL THEN COALESCE(T.remainingAmt, T.amount)
                        ELSE 0
                    END
                ) AS TotalPassbookBal,
                
                SUM(
                    CASE
                        WHEN T.comment IS NOT NULL OR T.docref IS NOT NULL THEN
                            CASE
                                WHEN COALESCE(T.reconciledAmt, 0) = 0 THEN T.amount
                                ELSE T.reconciledAmt
                            END
                        ELSE 0
                    END
                ) AS Reconciliation,

                SUM(
                    CASE 
                        WHEN T.bank_code = BD.BankName THEN T.amount
                        ELSE 0
                    END
                ) AS TotalTransactionAmount,

                CASE
                    WHEN SUM(CASE WHEN T.bank_code = BD.BankName THEN T.amount ELSE 0 END) = 0 THEN 0
                    ELSE ROUND(
                        SUM(
                            CASE
                                WHEN T.comment IS NOT NULL OR T.docref IS NOT NULL THEN
                                    CASE
                                        WHEN COALESCE(T.reconciledAmt, 0) = 0 THEN T.amount
                                        ELSE T.reconciledAmt
                                    END
                                ELSE 0
                            END
                        ) * 100.0 /
                        SUM(CASE WHEN T.bank_code = BD.BankName THEN T.amount ELSE 0 END),
                    2)
                END AS Percentage

            FROM cms_bank_depository BD
            LEFT JOIN cms_bank_transactions T ON T.account_no = BD.AccountNo
            GROUP BY BD.BankName
        ");

        return response()->json([
            'status' => 200,
            'data' => $query
        ]);
    }

    public function getAllPendingChecks(Request $request)
    {
        return $this->makeTreasuryRequest('ALL_PENDING_CHECKS', [
            'start' => $request->start,
            'end' => $request->end,
        ]);
    }

    public function getAllPendingOnlinePayment(Request $request)
    {
        return $this->makeTreasuryRequest('ALL_PENDING_ONLINE_PAYMENT', [
            'start' => $request->start,
            'end' => $request->end,
        ]);
    }

    public function getAllPendingCashPayment(Request $request)
    {
        return $this->makeTreasuryRequest('ALL_PENDING_CASH', [
            'start' => $request->start,
            'end' => $request->end,
        ]);
    }

    public function getCM1AcumaticaSummary()
    {
        return $this->getAcumaticaSummary('ROPALI CORPORATION');
    }

    public function getCM2AcumaticaSummary()
    {
        return $this->getAcumaticaSummary('MOTORBELLE CORPORATION');
    }

    public function getCM3AcumaticaSummary()
    {
        return $this->getAcumaticaSummary('MOTORALI CORPORATION');
    }

    public function getCM4AcumaticaSummary()
    {
        return $this->getAcumaticaSummary('MOTOROBEE CORPORATION');
    }

    private function getAcumaticaSummary(string $company)
    {
        $today = Carbon::now();
        $startDate = $today->copy()->subDays(2);

        $todayStr = $today->format("Y-m-d\TH:i:s");
        $startDateStr = $startDate->format("Y-m-d\TH:i:s");

        $filter = rawurlencode(
            "CATranExt_posted eq true and CATranExt_createdDateTime ge datetime'$startDateStr' and CATranExt_createdDateTime lt datetime'$todayStr'"
        );

        $baseUrl = rtrim($this->acumaticaBaseUrl, '/');
        $companyEncoded = rawurlencode($company);
        $url = "{$baseUrl}/odata/{$companyEncoded}/CashAccountDetails?\$format=json&\$filter={$filter}";

        try {
            $response = Http::withoutVerifying()
                ->withBasicAuth($this->acumaticaUsername, $this->acumaticaPassword)
                ->get($url);

            $data = $response->json();
            $summary = [];

            if (!empty($data['value'])) {
                foreach ($data['value'] as $cashAccount) {
                    $accountNo = trim($cashAccount['CashAccount']);
                    $credit = floatval($cashAccount['Disbursement']);
                    $debit = floatval($cashAccount['Receipt']);

                    if (!isset($summary[$accountNo])) {
                        $summary[$accountNo] = [
                            'CashAccount' => $accountNo,
                            'total_withdrawal' => 0,
                            'total_deposit' => 0
                        ];
                    }

                    $summary[$accountNo]['total_withdrawal'] += $credit;
                    $summary[$accountNo]['total_deposit'] += $debit;
                }
            }

            return response()->json([
                'status' => 200,
                'data' => array_values($summary)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Failed to fetch Acumatica data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPendingSOA()
    {
        $data = DB::table('cms_statement_of_account')
            ->select(DB::raw("
                CASE 
                    WHEN Status = 'APPROVED' THEN 'Approved' 
                    WHEN Status IS NULL THEN 'Pending' 
                    WHEN Status = 'DISAPPROVED' THEN 'Disapproved' 
                    ELSE 'OTHER' 
                END AS StatusCategory,
                COUNT(*) AS Count
            "))
            ->groupBy(DB::raw("
                CASE 
                    WHEN Status = 'APPROVED' THEN 'Approved' 
                    WHEN Status IS NULL THEN 'Pending' 
                    WHEN Status = 'DISAPPROVED' THEN 'Disapproved' 
                    ELSE 'OTHER' 
                END
            "))
            ->get();

        return response()->json($data);
    }

    public function getReconStatusCount()
    {
        $data = DB::table(DB::raw('(
            SELECT MultipleID, MIN(Status) as Status
            FROM cms_multiple_recon
            GROUP BY MultipleID
        ) as distinctRecon'))
            ->select(DB::raw("
                CASE 
                    WHEN Status = '1' THEN 'Approved'
                    WHEN Status = '0' THEN 'Disapproved'
                    WHEN Status IS NULL THEN 'Pending'
                    ELSE 'UNKNOWN'
                END AS StatusCategory,
                COUNT(*) as Count
            "))
            ->groupBy(DB::raw("
                CASE 
                    WHEN Status = '1' THEN 'Approved'
                    WHEN Status = '0' THEN 'Disapproved'
                    WHEN Status IS NULL THEN 'Pending'
                    ELSE 'UNKNOWN'
                END
            "))
            ->get();

        return response()->json($data);
    }

    public function getTransactionHistory(Request $request)
    {
        $account = trim($request->account);
        $search = $request->input('search.value', '');

        $query = DB::table('cms_bank_transactions as BT')
            ->leftJoin('cms_bank_code as BC', function($join) {
                $join->on(DB::raw("BT.additional_info"), 'like', DB::raw("'%' + RIGHT('00000' + CAST(BC.BranchCode AS VARCHAR), 5) + '%'"));
            })
            ->select(
                'BT.*',
                DB::raw("ISNULL(CAST(BC.BranchCode AS VARCHAR), '---') AS BranchCode"),
                DB::raw("ISNULL(BC.BranchName, '---') AS BranchName"),
                'BC.BranchAddress',
                DB::raw("CASE 
                    WHEN BT.additional_info LIKE '%CREDIT%' THEN 'Credit'
                    WHEN BT.additional_info LIKE '%DEBIT%' THEN 'Debit'
                    WHEN BT.additional_info LIKE '%DEPOSIT%' THEN 'Deposit'
                    ELSE 'Others'
                END AS Category")
            )
            ->where('BT.account_no', $account);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('BT.additional_info', 'like', "%$search%")
                  ->orWhere('BT.account_name', 'like', "%$search%");
            });
        }

        $totalCount = $query->count();
        $data = $query->orderBy('BT.transaction_date', 'desc')
                     ->skip($request->start ?? 0)
                     ->take($request->length ?? 10)
                     ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }
}