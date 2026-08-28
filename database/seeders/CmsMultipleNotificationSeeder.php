<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsMultipleNotificationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_multiple_notification')->insert([
            [
                'MultipleID' => 'MR-2026-0001',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 1,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0001',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 1,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0002',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 0,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(8)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0002',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 0,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(8)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0003',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 1,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(6)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0003',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 1,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(6)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0004',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 0,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0004',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 0,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0005',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 1,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
            ],
            [
                'MultipleID' => 'MR-2026-0005',
                'ApprovedBy' => 'admin@fundsync.com',
                'is_read'    => 0,
                'user_id'    => 1,
                'CreatedAt'  => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
