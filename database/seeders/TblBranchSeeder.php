<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblBranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tblbranch')->insert([
            ['branch_code' => 'RC001', 'branch_desc' => 'MAIN BRANCH', 'company_id' => 1, 'flag' => 'A', 'branch_region' => 'Luzon', 'BranchType' => 'HEAD OFFICE', 'BranchAddress' => '123 Ayala Avenue, Makati City, Metro Manila'],
            ['branch_code' => 'RC002', 'branch_desc' => 'CEBU BRANCH', 'company_id' => 1, 'flag' => 'A', 'branch_region' => 'Visayas', 'BranchType' => 'BRANCH', 'BranchAddress' => '45 Colon Street, Cebu City, Cebu'],
            ['branch_code' => 'RC003', 'branch_desc' => 'DAVAO BRANCH', 'company_id' => 1, 'flag' => 'A', 'branch_region' => 'Mindanao', 'BranchType' => 'BRANCH', 'BranchAddress' => '78 Claveria Street, Davao City, Davao del Sur'],
            ['branch_code' => 'MB001', 'branch_desc' => 'MAIN BRANCH', 'company_id' => 2, 'flag' => 'A', 'branch_region' => 'Luzon', 'BranchType' => 'HEAD OFFICE', 'BranchAddress' => '200 EDSA, Mandaluyong City, Metro Manila'],
            ['branch_code' => 'MB002', 'branch_desc' => 'ILOILO BRANCH', 'company_id' => 2, 'flag' => 'A', 'branch_region' => 'Visayas', 'BranchType' => 'BRANCH', 'BranchAddress' => '15 Iznart Street, Iloilo City, Iloilo'],
            ['branch_code' => 'MA001', 'branch_desc' => 'MAIN BRANCH', 'company_id' => 3, 'flag' => 'A', 'branch_region' => 'Luzon', 'BranchType' => 'HEAD OFFICE', 'BranchAddress' => '55 Quezon Avenue, Quezon City, Metro Manila'],
            ['branch_code' => 'MA002', 'branch_desc' => 'PAMPANGA BRANCH', 'company_id' => 3, 'flag' => 'A', 'branch_region' => 'Luzon', 'BranchType' => 'BRANCH', 'BranchAddress' => '88 MacArthur Highway, San Fernando, Pampanga'],
            ['branch_code' => 'MO001', 'branch_desc' => 'MAIN BRANCH', 'company_id' => 4, 'flag' => 'A', 'branch_region' => 'Luzon', 'BranchType' => 'HEAD OFFICE', 'BranchAddress' => '10 Bonifacio Global City, Taguig, Metro Manila'],
            ['branch_code' => 'MO002', 'branch_desc' => 'CAGAYAN DE ORO BRANCH', 'company_id' => 4, 'flag' => 'A', 'branch_region' => 'Mindanao', 'BranchType' => 'BRANCH', 'BranchAddress' => '25 Capistrano Street, Cagayan de Oro City, Misamis Oriental'],
            ['branch_code' => 'MO003', 'branch_desc' => 'ZAMBOANGA BRANCH', 'company_id' => 4, 'flag' => 'A', 'branch_region' => 'Mindanao', 'BranchType' => 'BRANCH', 'BranchAddress' => '30 Governor Camins Avenue, Zamboanga City, Zamboanga del Sur'],
        ]);
    }
}
