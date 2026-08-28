<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['AccountTag' => 'Operating Fund', 'Abbreviation' => 'OF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Payroll Fund', 'Abbreviation' => 'PF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Capital Expenditure', 'Abbreviation' => 'CAPEX', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Reserve Fund', 'Abbreviation' => 'RF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Tax Fund', 'Abbreviation' => 'TF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Project Fund', 'Abbreviation' => 'PROJ', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Contingency Fund', 'Abbreviation' => 'CF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Petty Cash Fund', 'Abbreviation' => 'PCF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Loan Fund', 'Abbreviation' => 'LF', 'CreatedBy' => 1, 'Status' => 1],
            ['AccountTag' => 'Investment Fund', 'Abbreviation' => 'IF', 'CreatedBy' => 1, 'Status' => 1],
        ];

        DB::table('cms_list_account_tag')->insert($tags);
    }
}
