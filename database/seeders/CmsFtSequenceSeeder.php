<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsFtSequenceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_ft_sequence')->insert([
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'TREASURY MANAGER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'Status'           => 'APPROVED',
                'FTID'             => 1,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'FINANCE DIRECTOR',
                'Sequence'         => 2,
                'PositionSequence' => 2,
                'Status'           => 'APPROVED',
                'FTID'             => 1,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(10)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'TREASURY MANAGER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'Status'           => 'APPROVED',
                'FTID'             => 2,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(9)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'FINANCE DIRECTOR',
                'Sequence'         => 2,
                'PositionSequence' => 2,
                'Status'           => 'APPROVED',
                'FTID'             => 2,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(9)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'TREASURY MANAGER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'Status'           => 'PENDING',
                'FTID'             => 3,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(8)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'TREASURY MANAGER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'Status'           => 'APPROVED',
                'FTID'             => 4,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(7)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'CFO',
                'Sequence'         => 3,
                'PositionSequence' => 3,
                'Status'           => 'APPROVED',
                'FTID'             => 4,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(7)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'TREASURY MANAGER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'Status'           => 'PENDING',
                'FTID'             => 5,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(6)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'FINANCE DIRECTOR',
                'Sequence'         => 2,
                'PositionSequence' => 2,
                'Status'           => 'APPROVED',
                'FTID'             => 6,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(5)->format('Y-m-d H:i:s'),
            ],
            [
                'Type'             => 'FUND_TRANSFER',
                'Signatory'        => 'TREASURY MANAGER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'Status'           => 'PENDING',
                'FTID'             => 7,
                'CreatedBy'        => 'admin@fundsync.com',
                'CreatedAt'        => Carbon::now()->subDays(4)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
