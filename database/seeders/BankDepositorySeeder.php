<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankDepositorySeeder extends Seeder
{
    public function run(): void
    {
        $depositories = [
            [
                'Company' => 'Ropali Holdings Corporation', 'BankName' => 'Banco de Oro Universal Bank',
                'AccountNo' => '001-234-5678', 'AccountName' => 'Ropali Holdings Corporation',
                'DepositType' => 'Savings Account', 'Description' => 'Main operating savings account',
                'AccountTag' => 'Operating Fund', 'InterestRate' => 0.25, 'BeginningBal' => 5000000.00,
                'MaintainingBal' => 500000.00, 'BankBranch' => 'Makati Main Branch',
                'BankStreet' => 'Ayala Avenue', 'BankCity' => 'Makati City', 'BankProvince' => 'Metro Manila',
                'DateOpen' => '2023-01-15', 'MaturityDate' => null,
                'DepContactNum' => '02-8-818-0000', 'DepContactPerson' => 'Mark Reyes',
                'DepEmailAdd' => 'mark.reyes@bdo.com.ph', 'DepPosition' => 'Branch Manager',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Motorcycle Inc.', 'BankName' => 'Bank of the Philippine Islands',
                'AccountNo' => '002-345-6789', 'AccountName' => 'Ropali Motorcycle Inc.',
                'DepositType' => 'Current Account', 'Description' => 'Operations current account',
                'AccountTag' => 'Operating Fund', 'InterestRate' => 0.10, 'BeginningBal' => 3000000.00,
                'MaintainingBal' => 250000.00, 'BankBranch' => 'Quezon City Branch',
                'BankStreet' => 'EDSA', 'BankCity' => 'Quezon City', 'BankProvince' => 'Metro Manila',
                'DateOpen' => '2023-02-01', 'MaturityDate' => null,
                'DepContactNum' => '02-8-895-0000', 'DepContactPerson' => 'Grace Tan',
                'DepEmailAdd' => 'grace.tan@bpi.com.ph', 'DepPosition' => 'Relationship Manager',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Properties Corp.', 'BankName' => 'Metropolitan Bank and Trust Company',
                'AccountNo' => '003-456-7890', 'AccountName' => 'Ropali Properties Corp.',
                'DepositType' => 'Time Deposit', 'Description' => '1-year time deposit',
                'AccountTag' => 'Reserve Fund', 'InterestRate' => 3.50, 'BeginningBal' => 10000000.00,
                'MaintainingBal' => 0.00, 'BankBranch' => 'Pasig Branch',
                'BankStreet' => 'Ortigas Avenue', 'BankCity' => 'Pasig City', 'BankProvince' => 'Metro Manila',
                'DateOpen' => '2023-03-01', 'MaturityDate' => '2024-03-01',
                'DepContactNum' => '02-8-857-0000', 'DepContactPerson' => 'Joy Cruz',
                'DepEmailAdd' => 'joy.cruz@metrobank.com.ph', 'DepPosition' => 'Account Officer',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Trading Company', 'BankName' => 'Philippine National Bank',
                'AccountNo' => '004-567-8901', 'AccountName' => 'Ropali Trading Company',
                'DepositType' => 'Current Account', 'Description' => 'Trade current account',
                'AccountTag' => 'Operating Fund', 'InterestRate' => 0.10, 'BeginningBal' => 2500000.00,
                'MaintainingBal' => 200000.00, 'BankBranch' => 'Cebu Main Branch',
                'BankStreet' => 'Osmena Boulevard', 'BankCity' => 'Cebu City', 'BankProvince' => 'Cebu',
                'DateOpen' => '2023-04-01', 'MaturityDate' => null,
                'DepContactNum' => '032-255-0000', 'DepContactPerson' => 'Ben Villanueva',
                'DepEmailAdd' => 'ben.villanueva@pnb.com.ph', 'DepPosition' => 'Branch Manager',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Ventures Inc.', 'BankName' => 'Land Bank of the Philippines',
                'AccountNo' => '005-678-9012', 'AccountName' => 'Ropali Ventures Inc.',
                'DepositType' => 'Savings Account', 'Description' => 'Davao operations savings',
                'AccountTag' => 'Project Fund', 'InterestRate' => 0.50, 'BeginningBal' => 1500000.00,
                'MaintainingBal' => 100000.00, 'BankBranch' => 'Davao Branch',
                'BankStreet' => 'J.P. Laurel Avenue', 'BankCity' => 'Davao City', 'BankProvince' => 'Davao del Sur',
                'DateOpen' => '2023-05-01', 'MaturityDate' => null,
                'DepContactNum' => '082-222-0000', 'DepContactPerson' => 'Lito Abad',
                'DepEmailAdd' => 'lito.abad@landbank.com', 'DepPosition' => 'Relationship Manager',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Construction Corp.', 'BankName' => 'Rizal Commercial Banking Corporation',
                'AccountNo' => '006-789-0123', 'AccountName' => 'Ropali Construction Corp.',
                'DepositType' => 'Current Account', 'Description' => 'Construction payables account',
                'AccountTag' => 'Capital Expenditure', 'InterestRate' => 0.10, 'BeginningBal' => 4000000.00,
                'MaintainingBal' => 300000.00, 'BankBranch' => 'Mandaluyong Branch',
                'BankStreet' => 'Shaw Boulevard', 'BankCity' => 'Mandaluyong City', 'BankProvince' => 'Metro Manila',
                'DateOpen' => '2023-06-01', 'MaturityDate' => null,
                'DepContactNum' => '02-8-580-0000', 'DepContactPerson' => 'Cynthia Lim',
                'DepEmailAdd' => 'cynthia.lim@rcbc.com', 'DepPosition' => 'Account Manager',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Logistics Inc.', 'BankName' => 'Security Bank Corporation',
                'AccountNo' => '007-890-1234', 'AccountName' => 'Ropali Logistics Inc.',
                'DepositType' => 'Payroll Account', 'Description' => 'Employee payroll account',
                'AccountTag' => 'Payroll Fund', 'InterestRate' => 0.25, 'BeginningBal' => 800000.00,
                'MaintainingBal' => 50000.00, 'BankBranch' => 'Laguna Branch',
                'BankStreet' => 'National Highway', 'BankCity' => 'Santa Rosa', 'BankProvince' => 'Laguna',
                'DateOpen' => '2023-07-01', 'MaturityDate' => null,
                'DepContactNum' => '049-302-0000', 'DepContactPerson' => 'Rina Ocampo',
                'DepEmailAdd' => 'rina.ocampo@securitybank.com', 'DepPosition' => 'Branch Officer',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Financial Services', 'BankName' => 'Union Bank of the Philippines',
                'AccountNo' => '008-901-2345', 'AccountName' => 'Ropali Financial Services',
                'DepositType' => 'Money Market Placement', 'Description' => 'Short-term money market',
                'AccountTag' => 'Investment Fund', 'InterestRate' => 4.00, 'BeginningBal' => 20000000.00,
                'MaintainingBal' => 1000000.00, 'BankBranch' => 'BGC Branch',
                'BankStreet' => '5th Avenue', 'BankCity' => 'Taguig City', 'BankProvince' => 'Metro Manila',
                'DateOpen' => '2023-08-01', 'MaturityDate' => '2024-08-01',
                'DepContactNum' => '02-8-841-0000', 'DepContactPerson' => 'Dennis Chan',
                'DepEmailAdd' => 'dennis.chan@unionbankph.com', 'DepPosition' => 'Investment Specialist',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Agri-Business Corp.', 'BankName' => 'Development Bank of the Philippines',
                'AccountNo' => '009-012-3456', 'AccountName' => 'Ropali Agri-Business Corp.',
                'DepositType' => 'Savings Account', 'Description' => 'Agri-business operations savings',
                'AccountTag' => 'Operating Fund', 'InterestRate' => 0.75, 'BeginningBal' => 1200000.00,
                'MaintainingBal' => 100000.00, 'BankBranch' => 'Pampanga Branch',
                'BankStreet' => 'MacArthur Highway', 'BankCity' => 'San Fernando', 'BankProvince' => 'Pampanga',
                'DateOpen' => '2023-09-01', 'MaturityDate' => null,
                'DepContactNum' => '045-455-0000', 'DepContactPerson' => 'Noel Santiago',
                'DepEmailAdd' => 'noel.santiago@dbp.ph', 'DepPosition' => 'Account Officer',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
            [
                'Company' => 'Ropali Technology Solutions', 'BankName' => 'China Banking Corporation',
                'AccountNo' => '010-123-4567', 'AccountName' => 'Ropali Technology Solutions',
                'DepositType' => 'Current Account', 'Description' => 'Technology operations account',
                'AccountTag' => 'Operating Fund', 'InterestRate' => 0.10, 'BeginningBal' => 2000000.00,
                'MaintainingBal' => 150000.00, 'BankBranch' => 'Ortigas Branch',
                'BankStreet' => 'ADB Avenue', 'BankCity' => 'Pasig City', 'BankProvince' => 'Metro Manila',
                'DateOpen' => '2023-10-01', 'MaturityDate' => null,
                'DepContactNum' => '02-8-885-0000', 'DepContactPerson' => 'Alice Sy',
                'DepEmailAdd' => 'alice.sy@chinabank.ph', 'DepPosition' => 'Relationship Manager',
                'Status' => 'Active', 'CreatedBy' => 'admin@fundsync.com',
            ],
        ];

        foreach ($depositories as $dep) {
            DB::table('cms_bank_depository')->insert(array_merge($dep, [
                'CreatedAt' => now(),
            ]));
        }
    }
}
