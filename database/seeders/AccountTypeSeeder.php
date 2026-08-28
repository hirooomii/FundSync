<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['AccountType' => 'Savings Account', 'Abbreviation' => 'SA', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Current Account', 'Abbreviation' => 'CA', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Time Deposit', 'Abbreviation' => 'TD', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Money Market Placement', 'Abbreviation' => 'MMP', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Trust Account', 'Abbreviation' => 'TA', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Foreign Currency Deposit', 'Abbreviation' => 'FCD', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Payroll Account', 'Abbreviation' => 'PA', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Escrow Account', 'Abbreviation' => 'EA', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Joint Account', 'Abbreviation' => 'JA', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountType' => 'Corporate Account', 'Abbreviation' => 'CRA', 'CreatedBy' => 1, 'Status' => 1],
        ];

        DB::table('cms_list_account_type')->insert($types);
    }
}
