<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['Company' => 'Ropali Holdings Corporation', 'Abbreviation' => 'RHC', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Motorcycle Inc.', 'Abbreviation' => 'RMI', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Properties Corp.', 'Abbreviation' => 'RPC', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Trading Company', 'Abbreviation' => 'RTC', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Ventures Inc.', 'Abbreviation' => 'RVI', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Construction Corp.', 'Abbreviation' => 'RCC', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Logistics Inc.', 'Abbreviation' => 'RLI', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Financial Services', 'Abbreviation' => 'RFS', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Agri-Business Corp.', 'Abbreviation' => 'RAB', 'CreatedBy' => 1, 'Status' => 1],
            ['Company' => 'Ropali Technology Solutions', 'Abbreviation' => 'RTS', 'CreatedBy' => 1, 'Status' => 1],
        ];

        DB::table('cms_list_company')->insert($companies);
    }
}
