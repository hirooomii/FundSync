<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSignatoriesSeeder extends Seeder
{
    public function run(): void
    {
        $accountNumbers = [
            '001-234-5678', '002-345-6789', '003-456-7890', '004-567-8901', '005-678-9012',
            '006-789-0123', '007-890-1234', '008-901-2345', '009-012-3456', '010-123-4567',
        ];

        $signatories = [
            ['EmployeeID' => 1, 'Name' => 'Admin User', 'Position' => 'Chief Executive Officer'],
            ['EmployeeID' => 2, 'Name' => 'Maria Santos', 'Position' => 'Chief Financial Officer'],
            ['EmployeeID' => 3, 'Name' => 'Juan Dela Cruz', 'Position' => 'Chief Operating Officer'],
            ['EmployeeID' => 4, 'Name' => 'Ana Reyes', 'Position' => 'Finance Manager'],
            ['EmployeeID' => 5, 'Name' => 'Jose Garcia', 'Position' => 'Treasury Analyst'],
            ['EmployeeID' => 6, 'Name' => 'Rosa Mendoza', 'Position' => 'Accounting Manager'],
            ['EmployeeID' => 7, 'Name' => 'Pedro Bautista', 'Position' => 'Senior Accountant'],
            ['EmployeeID' => 8, 'Name' => 'Luisa Torres', 'Position' => 'Junior Accountant'],
            ['EmployeeID' => 9, 'Name' => 'Carlos Ramos', 'Position' => 'Finance Officer'],
            ['EmployeeID' => 10, 'Name' => 'Elena Flores', 'Position' => 'Cashier'],
        ];

        foreach ($signatories as $i => $signatory) {
            DB::table('cms_bank_signatories')->insert(array_merge($signatory, [
                'AccountNo' => $accountNumbers[$i],
                'CreatedBy' => 'admin@fundsync.com',
                'CreatedAt' => now(),
                'Status' => 1,
            ]));
        }
    }
}
