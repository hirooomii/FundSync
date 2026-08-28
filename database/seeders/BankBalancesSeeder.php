<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $accountNumbers = [
            '001-234-5678', '002-345-6789', '003-456-7890', '004-567-8901', '005-678-9012',
            '006-789-0123', '007-890-1234', '008-901-2345', '009-012-3456', '010-123-4567',
        ];

        $openingBalances = [5000000, 3000000, 10000000, 2500000, 1500000, 4000000, 800000, 20000000, 1200000, 2000000];

        foreach ($accountNumbers as $i => $accountNo) {
            $opening = $openingBalances[$i];
            $movement = round(rand(-200000, 500000), 2);
            $closing = $opening + $movement;
            $available = $closing * 0.95;

            DB::table('cms_bank_balances')->insert([
                'account_no' => $accountNo,
                'currency' => 'PHP',
                'opening_balance' => $opening,
                'closing_balance' => $closing,
                'available_balance' => $available,
                'transaction_date' => now()->subDays(30 - ($i * 3))->toDateString(),
                'SOAID' => $i + 1,
            ]);

            DB::table('cms_bank_balances_pending')->insert([
                'account_no' => $accountNo,
                'currency' => 'PHP',
                'opening_balance' => $closing,
                'closing_balance' => null,
                'available_balance' => $available,
                'transaction_date' => now()->toDateString(),
                'SOAID' => null,
            ]);
        }
    }
}
