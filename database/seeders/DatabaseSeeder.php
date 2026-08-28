<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PositionSeeder::class,
            CompanySeeder::class,
            BankSeeder::class,
            AccountTypeSeeder::class,
            AccountTagSeeder::class,
            UserSeeder::class,
            BankCashAccountSeeder::class,
            BankDepositorySeeder::class,
            BankSignatoriesSeeder::class,
            StatementOfAccountSeeder::class,
            BankTransactionsSeeder::class,
            BankTransactionsPendingSeeder::class,
            BankBalancesSeeder::class,
            TblBranchSeeder::class,
            CmsBankCodeSeeder::class,
            CmsApprovalMatrixSeeder::class,
            CmsCashPositionSeeder::class,
            CmsCashaccountDetailsSeeder::class,
            CmsFtApprovalSeeder::class,
            CmsFtSequenceSeeder::class,
            CmsFundVoucherSeeder::class,
            CmsMultipleReconSeeder::class,
            CmsVoucherDetailsSeeder::class,
            CmsVoucherLogsSeeder::class,
            CmsMultipleNotificationSeeder::class,
        ]);
    }
}
