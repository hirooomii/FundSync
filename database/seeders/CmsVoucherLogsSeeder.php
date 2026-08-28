<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsVoucherLogsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_voucher_logs')->insert([
            [
                'Reference' => 'PV-2026-0001',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'CREATED',
                'Company'   => 'ROPALI CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0001',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'APPROVED',
                'Company'   => 'ROPALI CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(9)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0002',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'CREATED',
                'Company'   => 'ROPALI CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(9)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0003',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'CREATED',
                'Company'   => 'MOTORBELLE CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(8)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0003',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'APPROVED',
                'Company'   => 'MOTORBELLE CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(7)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0003',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'PRINTED',
                'Company'   => 'MOTORBELLE CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(6)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0005',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'CREATED',
                'Company'   => 'MOTORALI CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(6)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0007',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'CREATED',
                'Company'   => 'MOTOROBEE CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0007',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'APPROVED',
                'Company'   => 'MOTOROBEE CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s'),
            ],
            [
                'Reference' => 'PV-2026-0009',
                'CreatedBy' => 'admin@fundsync.com',
                'Type'      => 'CREATED',
                'Company'   => 'ROPALI CORPORATION',
                'CreatedAt' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
