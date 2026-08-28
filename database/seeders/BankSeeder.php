<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['Bank' => 'Banco de Oro Universal Bank', 'Abbreviation' => 'BDO', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Bank of the Philippine Islands', 'Abbreviation' => 'BPI', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Metropolitan Bank and Trust Company', 'Abbreviation' => 'MBTC', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Philippine National Bank', 'Abbreviation' => 'PNB', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Land Bank of the Philippines', 'Abbreviation' => 'LBP', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Development Bank of the Philippines', 'Abbreviation' => 'DBP', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'China Banking Corporation', 'Abbreviation' => 'CBC', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Security Bank Corporation', 'Abbreviation' => 'SBC', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Rizal Commercial Banking Corporation', 'Abbreviation' => 'RCBC', 'CreatedBy' => 1, 'Status' => 1],
            ['Bank' => 'Union Bank of the Philippines', 'Abbreviation' => 'UBP', 'CreatedBy' => 1, 'Status' => 1],
        ];

        DB::table('cms_list_bank')->insert($banks);
    }
}
