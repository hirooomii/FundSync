<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsApprovalMatrixSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_approval_matrix')->insert([
            [
                'Signatory'        => 'TREASURY MANAGER',
                'Type'             => 'FUND_TRANSFER',
                'Sequence'         => 1,
                'PositionSequence' => 1,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'FINANCE DIRECTOR',
                'Type'             => 'FUND_TRANSFER',
                'Sequence'         => 2,
                'PositionSequence' => 2,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'CFO',
                'Type'             => 'FUND_TRANSFER',
                'Sequence'         => 3,
                'PositionSequence' => 3,
                'IsReturnable'     => 0,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'ACCOUNTING SUPERVISOR',
                'Type'             => 'VOUCHER',
                'Sequence'         => 1,
                'PositionSequence' => 4,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(29)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'ACCOUNTING MANAGER',
                'Type'             => 'VOUCHER',
                'Sequence'         => 2,
                'PositionSequence' => 5,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(29)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'FINANCE DIRECTOR',
                'Type'             => 'VOUCHER',
                'Sequence'         => 3,
                'PositionSequence' => 6,
                'IsReturnable'     => 0,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(29)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'TREASURY ANALYST',
                'Type'             => 'SOA',
                'Sequence'         => 1,
                'PositionSequence' => 7,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(28)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'TREASURY MANAGER',
                'Type'             => 'SOA',
                'Sequence'         => 2,
                'PositionSequence' => 8,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(28)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'TREASURY ANALYST',
                'Type'             => 'CASH_POSITION',
                'Sequence'         => 1,
                'PositionSequence' => 9,
                'IsReturnable'     => 1,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(27)->format('Y-m-d H:i:s'),
            ],
            [
                'Signatory'        => 'CFO',
                'Type'             => 'CASH_POSITION',
                'Sequence'         => 2,
                'PositionSequence' => 10,
                'IsReturnable'     => 0,
                'IsDisapproved'    => 0,
                'CreatedAt'        => Carbon::now()->subDays(27)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
