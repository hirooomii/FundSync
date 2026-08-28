<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsFtApprovalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_ft_approval')->insert([
            [
                'Serial'          => 'FT-2026-0001',
                'Reference'       => 'FT-2026-0001',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'ROPALI CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 1,
                'CreatedAt'       => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0002',
                'Reference'       => 'FT-2026-0002',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'ROPALI CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 1,
                'CreatedAt'       => Carbon::now()->subDays(9)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0003',
                'Reference'       => 'FT-2026-0003',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTORBELLE CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 0,
                'CreatedAt'       => Carbon::now()->subDays(8)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0004',
                'Reference'       => 'FT-2026-0004',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTORBELLE CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 1,
                'CreatedAt'       => Carbon::now()->subDays(7)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0005',
                'Reference'       => 'FT-2026-0005',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTORALI CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 0,
                'CreatedAt'       => Carbon::now()->subDays(6)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0006',
                'Reference'       => 'FT-2026-0006',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTORALI CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 1,
                'CreatedAt'       => Carbon::now()->subDays(5)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0007',
                'Reference'       => 'FT-2026-0007',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTOROBEE CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 0,
                'CreatedAt'       => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0008',
                'Reference'       => 'FT-2026-0008',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTOROBEE CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 1,
                'CreatedAt'       => Carbon::now()->subDays(3)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0009',
                'Reference'       => 'FT-2026-0009',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'ROPALI CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 0,
                'CreatedAt'       => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
            ],
            [
                'Serial'          => 'FT-2026-0010',
                'Reference'       => 'FT-2026-0010',
                'Type'            => 'FUND_TRANSFER',
                'Company'         => 'MOTORALI CORPORATION',
                'CreatedBy'       => 'admin@fundsync.com',
                'IsDisapproved'   => 0,
                'IsFullyApproved' => 0,
                'CreatedAt'       => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
