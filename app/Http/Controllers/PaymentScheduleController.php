<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentScheduleController extends Controller
{

    private $baseUri;
    private $appKey;
    private $clientId;
    private $secretKey;

    public function __construct()
    {
        $this->baseUri = env('TREASURY_BASE_URI');
        $this->appKey = env('TREASURY_APP_KEY');
        $this->clientId = env('TREASURY_CLIENT_ID');
        $this->secretKey = env('TREASURY_SECRET_KEY');
    }

    public function index()
    {
        return Inertia::render('Schedule/PaymentSchedule');
    }

    private function getToken()
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(100)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Client-ID' => $this->clientId,
                    'Secret-Key' => $this->secretKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUri . '/ApiCMS/FetchRequest', [
                    'appKey' => $this->appKey,
                    'action' => 'GET_TOKEN',
                ]);

            $result = $response->json();
            return $result['data']['token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get token: ' . $e->getMessage());
            return null;
        }
    }

    public function getDatesPending(Request $request)
    {
        $request->validate([
            'company' => 'required|string',
            'start' => 'required|string',
            'end' => 'required|string',
        ]);

        try {
            $token = $this->getToken();
            
            if (!$token) {
                return response()->json(['error' => 'Failed to authenticate'], 401);
            }

            $response = Http::withOptions(['verify' => false])
                ->timeout(100)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUri . '/ApiCMS/FetchRequest', [
                    'appKey' => $this->appKey,
                    'action' => 'GET_PAYMENT_SCHEDULE',
                    'data' => [
                        'company' => $request->company,
                        'start' => $request->start,
                        'end' => $request->end,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $events = [];

                foreach ($data['data'] as $result) {
                    $eventDate = date('Y-m-d', strtotime($result['C_CheckDate']));
                    $amount = floatval($result['alterAmount'] ?? $result['Amount']);

                    if (!isset($events[$eventDate])) {
                        $events[$eventDate] = [];
                    }

                    $events[$eventDate][] = [
                        'title' => number_format($amount, 2),
                        'amount' => $amount,
                        'ID' => $result['RecID'],
                        'CheckID' => $result['checkId'],
                        'EcpfNo' => $result['EcpfNo'],
                        'PayingCompany' => $result['PayingCompany'],
                        'Purpose' => $result['Purpose'],
                        'PaymentMethod' => $result['PaymentMethod'],
                        'AccountNumber' => $result['AccountNumber'],
                        'CashAccount' => $result['CashAccount'],
                        'PayeeName' => $result['PayeeName'],
                        'CheckNumber' => $result['CheckNumber'],
                        'Payee' => $result['PayeeName'],
                        'DFEAF' => $result['dpeaf_no'],
                        'FundingStatus' => $result['FundingStatus'],
                        'PaymentType' => $result['PaymentType'],
                    ];
                }

                return response()->json($events);
            }

            return response()->json(['error' => 'Failed to fetch data'], $response->status());
        } catch (\Exception $e) {
            Log::error('GetDatesPending Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }

    public function getDueDates(Request $request)
    {
        $request->validate([
            'company' => 'required|string',
        ]);

        try {
            $token = $this->getToken();
            
            if (!$token) {
                return response()->json(['error' => 'Failed to authenticate'], 401);
            }

            $response = Http::withOptions(['verify' => false])
                ->timeout(100)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUri . '/ApiCMS/FetchRequest', [
                    'appKey' => $this->appKey,
                    'action' => 'GET_DUE_DATES',
                    'data' => [
                        'company' => $request->company,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $five = [];
                $fifteen = [];
                $thirty = [];

                foreach ($data['data'] as $result) {
                    $amount = floatval($result['alterAmount'] ?? $result['Amount']);

                    $entry = [
                        'ID' => $result['RecID'],
                        'Date' => $result['DueDate'],
                        'title' => number_format($amount, 2),
                        'amount' => $amount,
                        'EcpfNo' => $result['EcpfNo'],
                        'PayingCompany' => $result['PayingCompany'],
                        'Purpose' => $result['Purpose'],
                        'PaymentMethod' => $result['PaymentMethod'],
                        'Amount' => $result['Amount'],
                        'AccountNumber' => $result['AccountNumber'],
                        'CashAccount' => $result['CashAccount'],
                        'PayeeName' => $result['PayeeName'],
                        'CheckNumber' => $result['CheckNumber'],
                        'Payee' => $result['PayeeName'],
                        'DFEAF' => $result['dpeaf_no'],
                        'FundingStatus' => $result['FundingStatus'],
                        'PaymentType' => $result['PaymentType'],
                        'CheckID' => $result['checkId'],
                    ];

                    switch ($result['Due']) {
                        case 5:
                            $five[] = $entry;
                            break;
                        case 15:
                            $fifteen[] = $entry;
                            break;
                        case 30:
                            $thirty[] = $entry;
                            break;
                    }
                }

                return response()->json([
                    '5' => $five,
                    '15' => $fifteen,
                    '30' => $thirty,
                ]);
            }

            return response()->json(['error' => 'Failed to fetch data'], $response->status());
        } catch (\Exception $e) {
            Log::error('GetDueDates Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }

    public function updateFundingStatus(Request $request)
    {
        $request->validate([
            'funds' => 'required|array',
            'funds.*.id' => 'required',
            'funds.*.method' => 'required|string',
            'funds.*.funding' => 'required|string',
        ]);

        try {
            $token = $this->getToken();
            
            if (!$token) {
                return response()->json(['error' => 'Failed to authenticate'], 401);
            }

            $response = Http::withOptions(['verify' => false])
                ->timeout(100)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUri . '/ApiCMS/FetchRequest', [
                    'appKey' => $this->appKey,
                    'action' => 'UPDATE_FUNDING_STATUS',
                    'data' => [
                        'data' => $request->funds,
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json($result);
            }

            return response()->json(['error' => 'Failed to update funding status'], $response->status());
        } catch (\Exception $e) {
            Log::error('UpdateFundingStatus Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }
}
