<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $accountNumbers = [
            '001-234-5678', '002-345-6789', '003-456-7890', '004-567-8901', '005-678-9012',
            '006-789-0123', '007-890-1234', '008-901-2345', '009-012-3456', '010-123-4567',
        ];

        $bankCodes = ['BDO', 'BPI', 'MBTC', 'PNB', 'LBP', 'RCBC', 'SBC', 'UBP', 'DBP', 'CBC'];
        $types = ['Debit', 'Credit', 'Transfer', 'Withdrawal', 'Deposit', 'Interest', 'Debit', 'Credit', 'Transfer', 'Deposit'];
        $descriptions = [
            'Payment for office supplies', 'Collection from client', 'Interbank transfer',
            'ATM withdrawal', 'Cash deposit', 'Monthly interest earned',
            'Utility bills payment', 'Sales collection', 'Fund transfer to subsidiary', 'Cash deposit from operations',
        ];
        $companies = [
            'Ropali Holdings Corporation', 'Ropali Motorcycle Inc.', 'Ropali Properties Corp.',
            'Ropali Trading Company', 'Ropali Ventures Inc.', 'Ropali Construction Corp.',
            'Ropali Logistics Inc.', 'Ropali Financial Services', 'Ropali Agri-Business Corp.',
            'Ropali Technology Solutions',
        ];

        $runningBal = 5000000.00;

        foreach ($accountNumbers as $i => $accountNo) {
            $amount = round(rand(10000, 500000) / 100, 2);
            $isCredit = in_array($types[$i], ['Credit', 'Deposit', 'Interest', 'Collection from client', 'Sales collection']);
            $runningBal = $isCredit ? $runningBal + $amount : $runningBal - $amount;

            DB::table('cms_bank_transactions')->insert([
                'account_no' => $accountNo,
                'bank_code' => $bankCodes[$i],
                'transaction_date' => now()->subDays(30 - ($i * 3))->toDateString(),
                'amount' => $amount,
                'debit_or_credit' => $isCredit ? 'Credit' : 'Debit',
                'transaction_type' => $types[$i],
                'description' => $descriptions[$i],
                'reference' => 'TXN-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'additional_info' => 'Auto-generated transaction',
                'company' => $companies[$i],
                'docref' => null,
                'bindAt' => now()->subDays(30 - ($i * 3)),
                'bindBy' => 'admin@fundsync.com',
                'runningbal' => round($runningBal, 2),
                'reconciledAmt' => $amount,
                'remainingAmt' => 0,
                'comment' => null,
                'decimal' => 2,
                'note' => null,
                'SOAID' => $i + 1,
            ]);
        }
    }
}
