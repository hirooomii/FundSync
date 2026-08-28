<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['POSITIONCODE' => 1001, 'POSITIONDESC' => 'Chief Executive Officer'],
            ['POSITIONCODE' => 1002, 'POSITIONDESC' => 'Chief Financial Officer'],
            ['POSITIONCODE' => 1003, 'POSITIONDESC' => 'Chief Operating Officer'],
            ['POSITIONCODE' => 1004, 'POSITIONDESC' => 'Finance Manager'],
            ['POSITIONCODE' => 1005, 'POSITIONDESC' => 'Treasury Analyst'],
            ['POSITIONCODE' => 1006, 'POSITIONDESC' => 'Accounting Manager'],
            ['POSITIONCODE' => 1007, 'POSITIONDESC' => 'Senior Accountant'],
            ['POSITIONCODE' => 1008, 'POSITIONDESC' => 'Junior Accountant'],
            ['POSITIONCODE' => 1009, 'POSITIONDESC' => 'Finance Officer'],
            ['POSITIONCODE' => 1010, 'POSITIONDESC' => 'Cashier'],
        ];

        DB::table('tbl_position')->insert($positions);
    }
}
