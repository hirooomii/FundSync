<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankCashAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Company' => 'Ropali Holdings Corporation', 'Branch' => 'Makati Main', 'BranchID' => 'MKT-001', 'CashAccount' => 'BDO-Savings', 'Description' => 'BDO Savings Account - Main', 'IsActive' => 1, 'AccountNo' => '001-234-5678', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Motorcycle Inc.', 'Branch' => 'Quezon City', 'BranchID' => 'QC-001', 'CashAccount' => 'BPI-Current', 'Description' => 'BPI Current Account - Operations', 'IsActive' => 1, 'AccountNo' => '002-345-6789', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Properties Corp.', 'Branch' => 'Pasig', 'BranchID' => 'PSG-001', 'CashAccount' => 'MBTC-Savings', 'Description' => 'Metrobank Savings Account', 'IsActive' => 1, 'AccountNo' => '003-456-7890', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Trading Company', 'Branch' => 'Cebu', 'BranchID' => 'CEB-001', 'CashAccount' => 'PNB-Current', 'Description' => 'PNB Current Account - Cebu', 'IsActive' => 1, 'AccountNo' => '004-567-8901', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Ventures Inc.', 'Branch' => 'Davao', 'BranchID' => 'DVO-001', 'CashAccount' => 'LBP-Savings', 'Description' => 'Landbank Savings Account - Davao', 'IsActive' => 1, 'AccountNo' => '005-678-9012', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Construction Corp.', 'Branch' => 'Mandaluyong', 'BranchID' => 'MDL-001', 'CashAccount' => 'RCBC-Current', 'Description' => 'RCBC Current Account', 'IsActive' => 1, 'AccountNo' => '006-789-0123', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Logistics Inc.', 'Branch' => 'Laguna', 'BranchID' => 'LAG-001', 'CashAccount' => 'SBC-Payroll', 'Description' => 'Security Bank Payroll Account', 'IsActive' => 1, 'AccountNo' => '007-890-1234', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Financial Services', 'Branch' => 'BGC Taguig', 'BranchID' => 'BGC-001', 'CashAccount' => 'UBP-Savings', 'Description' => 'UnionBank Savings Account', 'IsActive' => 1, 'AccountNo' => '008-901-2345', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Agri-Business Corp.', 'Branch' => 'Pampanga', 'BranchID' => 'PAM-001', 'CashAccount' => 'DBP-Savings', 'Description' => 'DBP Savings Account - Pampanga', 'IsActive' => 1, 'AccountNo' => '009-012-3456', 'CreatedBy' => 'admin@fundsync.com'],
            ['Company' => 'Ropali Technology Solutions', 'Branch' => 'Ortigas', 'BranchID' => 'ORT-001', 'CashAccount' => 'CBC-Current', 'Description' => 'China Bank Current Account', 'IsActive' => 1, 'AccountNo' => '010-123-4567', 'CreatedBy' => 'admin@fundsync.com'],
        ];

        foreach ($accounts as $account) {
            DB::table('bank_cash_account')->insert(array_merge($account, [
                'CreatedAt' => now(),
            ]));
        }
    }
}
