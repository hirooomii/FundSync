<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CmsBankCodeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cms_bank_code')->insert([
            [
                'BranchCode'    => '01001',
                'BranchName'    => 'BDO MAKATI BRANCH',
                'BranchAddress' => 'BDO Building, Ayala Avenue corner Paseo de Roxas, Makati City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '02001',
                'BranchName'    => 'BPI ORTIGAS BRANCH',
                'BranchAddress' => 'BPI Building, ADB Avenue, Ortigas Center, Pasig City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(29)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '03001',
                'BranchName'    => 'METROBANK BGC BRANCH',
                'BranchAddress' => 'Metrobank Plaza, Bonifacio Global City, Taguig',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(28)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '04001',
                'BranchName'    => 'LANDBANK QUEZON CITY BRANCH',
                'BranchAddress' => 'Landbank Plaza, 1598 M.H. del Pilar corner Dr. J. Quintos Street, Malate, Manila',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(27)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '05001',
                'BranchName'    => 'RCBC MANILA BRANCH',
                'BranchAddress' => 'RCBC Plaza, 6819 Ayala Avenue, Makati City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(26)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '06001',
                'BranchName'    => 'PNB INTRAMUROS BRANCH',
                'BranchAddress' => 'PNB Financial Center, Pres. Diosdado Macapagal Boulevard, Pasay City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(25)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '07001',
                'BranchName'    => 'UNIONBANK CEBU BRANCH',
                'BranchAddress' => 'UnionBank Building, Mindanao Avenue, Cebu Business Park, Cebu City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(24)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '08001',
                'BranchName'    => 'CHINABANK BINONDO BRANCH',
                'BranchAddress' => 'Chinabank Building, 8745 Paseo de Roxas, Makati City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(23)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '09001',
                'BranchName'    => 'SECURITY BANK ALABANG BRANCH',
                'BranchAddress' => 'Security Bank Centre, 6776 Ayala Avenue, Makati City',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(22)->format('Y-m-d H:i:s'),
            ],
            [
                'BranchCode'    => '10001',
                'BranchName'    => 'EASTWEST BANK DAVAO BRANCH',
                'BranchAddress' => 'EastWest Corporate Center, The Beaufort, 5th Avenue cor. 23rd Street, BGC, Taguig',
                'Status'        => 1,
                'CreatedBy'     => 'SYSTEM',
                'CreatedAt'     => Carbon::now()->subDays(21)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
