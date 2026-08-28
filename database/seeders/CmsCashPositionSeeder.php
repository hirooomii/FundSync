<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsCashPositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_cash_position')->insert([
            [
                'RefDate'       => Carbon::now()->subDays(1)->format('Y-m-d'),
                'CashAccount'   => 'BDO-001',
                'CashAvailable' => 3500000.00,
                'CashForRepo'   => 500000.00,
                'TotalCash'     => 4000000.00,
                'Company'       => 'ROPALI CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(2)->format('Y-m-d'),
                'CashAccount'   => 'BPI-002',
                'CashAvailable' => 2800000.00,
                'CashForRepo'   => 200000.00,
                'TotalCash'     => 3000000.00,
                'Company'       => 'ROPALI CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(3)->format('Y-m-d'),
                'CashAccount'   => 'METROBANK-003',
                'CashAvailable' => 1500000.00,
                'CashForRepo'   => 0.00,
                'TotalCash'     => 1500000.00,
                'Company'       => 'MOTORBELLE CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(3)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(4)->format('Y-m-d'),
                'CashAccount'   => 'LANDBANK-004',
                'CashAvailable' => 4200000.00,
                'CashForRepo'   => 800000.00,
                'TotalCash'     => 5000000.00,
                'Company'       => 'MOTORBELLE CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(5)->format('Y-m-d'),
                'CashAccount'   => 'RCBC-005',
                'CashAvailable' => 750000.00,
                'CashForRepo'   => 250000.00,
                'TotalCash'     => 1000000.00,
                'Company'       => 'MOTORALI CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(5)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(8)->format('Y-m-d'),
                'CashAccount'   => 'PNB-006',
                'CashAvailable' => 1200000.00,
                'CashForRepo'   => 0.00,
                'TotalCash'     => 1200000.00,
                'Company'       => 'MOTORALI CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(8)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(9)->format('Y-m-d'),
                'CashAccount'   => 'UNIONBANK-007',
                'CashAvailable' => 2100000.00,
                'CashForRepo'   => 400000.00,
                'TotalCash'     => 2500000.00,
                'Company'       => 'MOTOROBEE CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(9)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(10)->format('Y-m-d'),
                'CashAccount'   => 'CHINABANK-008',
                'CashAvailable' => 900000.00,
                'CashForRepo'   => 100000.00,
                'TotalCash'     => 1000000.00,
                'Company'       => 'MOTOROBEE CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(11)->format('Y-m-d'),
                'CashAccount'   => 'SECURITYBANK-009',
                'CashAvailable' => 3000000.00,
                'CashForRepo'   => 500000.00,
                'TotalCash'     => 3500000.00,
                'Company'       => 'ROPALI CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(11)->format('Y-m-d H:i:s'),
            ],
            [
                'RefDate'       => Carbon::now()->subDays(12)->format('Y-m-d'),
                'CashAccount'   => 'EASTWEST-010',
                'CashAvailable' => 600000.00,
                'CashForRepo'   => 0.00,
                'TotalCash'     => 600000.00,
                'Company'       => 'MOTORALI CORPORATION',
                'CreatedBy'     => 'admin@fundsync.com',
                'CreatedAt'     => Carbon::now()->subDays(12)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
