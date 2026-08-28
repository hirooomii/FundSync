<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatementOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accountNumbers = [
            '001-234-5678', '002-345-6789', '003-456-7890', '004-567-8901', '005-678-9012',
            '006-789-0123', '007-890-1234', '008-901-2345', '009-012-3456', '010-123-4567',
        ];

        $statuses = ['Approved', 'Pending', 'For Review', 'Approved', 'Approved', 'Pending', 'Approved', 'For Review', 'Approved', 'Pending'];
        $months = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06', '2024-07', '2024-08', '2024-09', '2024-10'];

        foreach ($accountNumbers as $i => $accountNo) {
            DB::table('cms_statement_of_account')->insert([
                'SOA' => 'SOA-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'Remarks' => 'Monthly bank statement for ' . $months[$i],
                'PassbookBal' => round(rand(500000, 20000000) / 100, 2),
                'TransacBy' => $i + 1,
                'TransacAt' => now()->subMonths(10 - $i),
                'AccountNo' => $accountNo,
                'CreatedBy' => 'admin@fundsync.com',
                'CreatedAt' => now()->subMonths(10 - $i),
                'Status' => $statuses[$i],
                'Approved' => $statuses[$i] === 'Approved' ? now()->subMonths(10 - $i)->addDays(2) : null,
                'IsExcel' => 0,
                'Approver' => $statuses[$i] === 'Approved' ? 1 : null,
                'Attachment' => 1,
            ]);
        }
    }
}
