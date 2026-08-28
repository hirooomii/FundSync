<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankTransactionsPendingSeeder extends Seeder
{
    public function run(): void
    {
        $accountNumbers = [
            '001-234-5678', '002-345-6789', '003-456-7890', '004-567-8901', '005-678-9012',
            '006-789-0123', '007-890-1234', '008-901-2345', '009-012-3456', '010-123-4567',
        ];

        $bankCodes = ['BDO', 'BPI', 'MBTC', 'PNB', 'LBP', 'RCBC', 'SBC', 'UBP', 'DBP', 'CBC'];
        $types = ['Debit', 'Credit', 'Deposit', 'Withdrawal', 'Transfer', 'Debit', 'Credit', 'Deposit', 'Transfer', 'Debit'];
        $descriptions = [
            'Pending supplier payment', 'Pending customer refund', 'Uncleared deposit',
            'Pending ATM withdrawal', 'Pending interbank transfer', 'Unprocessed utility payment',
            'Pending collection receipt', 'Cash deposit under verification', 'Pending subsidiary transfer', 'Unposted debit memo',
        ];
        $companies = [
            'Ropali Holdings Corporation', 'Ropali Motorcycle Inc.', 'Ropali Properties Corp.',
            'Ropali Trading Company', 'Ropali Ventures Inc.', 'Ropali Construction Corp.',
            'Ropali Logistics Inc.', 'Ropali Financial Services', 'Ropali Agri-Business Corp.',
            'Ropali Technology Solutions',
        ];

        foreach ($accountNumbers as $i => $accountNo) {
            $amount = round(rand(5000, 250000) / 100, 2);
            $isCredit = in_array($types[$i], ['Credit', 'Deposit']);

            DB::table('cms_bank_transactions_pending')->insert([
                'account_no' => $accountNo,
                'bank_code' => $bankCodes[$i],
                'transaction_date' => now()->subDays(5 - ($i % 5))->toDateString(),
                'amount' => $amount,
                'debit_or_credit' => $isCredit ? 'Credit' : 'Debit',
                'transaction_type' => $types[$i],
                'description' => $descriptions[$i],
                'reference' => 'PND-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'additional_info' => 'Pending verification',
                'company' => $companies[$i],
                'docref' => null,
                'bindAt' => null,
                'bindBy' => null,
                'runningbal' => null,
                'reconciledAmt' => null,
                'remainingAmt' => $amount,
                'comment' => 'Awaiting approval',
                'decimal' => 2,
                'note' => 'Pending clearance',
                'SOAID' => null,
            ]);
        }
    }
}
