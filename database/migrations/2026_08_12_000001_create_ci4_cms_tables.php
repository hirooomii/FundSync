<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. tblbranch
        Schema::create('tblbranch', function (Blueprint $table) {
            $table->increments('branchid');
            $table->string('branch_code', 20)->nullable();
            $table->string('branch_desc', 100)->nullable();
            $table->integer('company_id')->nullable();
            $table->string('flag', 5)->nullable();
            $table->string('branch_region', 50)->nullable();
            $table->integer('Oas_BranchId')->nullable();
            $table->decimal('Latitude', 10, 6)->nullable();
            $table->decimal('Longitude', 10, 6)->nullable();
            $table->string('ParentBranch', 20)->nullable();
            $table->string('BranchType', 50)->nullable();
            $table->string('BranchAddress', 255)->nullable();
            $table->string('Cluster', 50)->nullable();
            $table->string('AreaManager', 100)->nullable();
        });

        // 2. cms_bank_code
        Schema::create('cms_bank_code', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('BranchName', 100)->nullable();
            $table->string('BranchCode', 20)->nullable();
            $table->string('BranchAddress', 255)->nullable();
            $table->integer('Status')->nullable()->default(1);
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

        // 3. cms_approval_matrix
        Schema::create('cms_approval_matrix', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Signatory', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->integer('Sequence')->nullable();
            $table->integer('PositionSequence')->nullable();
            $table->tinyInteger('IsReturnable')->nullable()->default(0);
            $table->tinyInteger('IsDisapproved')->nullable()->default(0);
            $table->dateTime('CreatedAt')->nullable();
        });

        // 4. cms_branch_tagging
        Schema::create('cms_branch_tagging', function (Blueprint $table) {
            $table->increments('RecID');
            $table->integer('TrainorID')->nullable();
            $table->string('BranchCode', 20)->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('UpdatedBy', 100)->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->tinyInteger('IsDeleted')->nullable()->default(0);
        });

        // 5. cms_cash_position
        Schema::create('cms_cash_position', function (Blueprint $table) {
            $table->increments('RecID');
            $table->date('RefDate')->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->decimal('CashAvailable', 18, 2)->nullable();
            $table->decimal('CashForRepo', 18, 2)->nullable();
            $table->decimal('TotalCash', 18, 2)->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('Company', 100)->nullable();
        });

        // 6. cms_cash_record_txn_details
        Schema::create('cms_cash_record_txn_details', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('CashAccount', 50)->nullable();
            $table->date('TxnDate')->nullable();
            $table->text('CollectionDetails')->nullable();
            $table->text('PaymentDetails')->nullable();
            $table->text('DepositDetails')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

        // 7. cms_cash_record_main
        Schema::create('cms_cash_record_main', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Company', 100)->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->decimal('Collection', 18, 2)->nullable();
            $table->text('CollectionWords')->nullable();
            $table->decimal('Ending', 18, 2)->nullable();
            $table->text('EndingWords')->nullable();
            $table->decimal('Deposit', 18, 2)->nullable();
            $table->text('DepositWords')->nullable();
            $table->decimal('Payment', 18, 2)->nullable();
            $table->text('PaymentWords')->nullable();
            $table->decimal('Balance', 18, 2)->nullable();
            $table->text('BalanceWords')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->text('Reason')->nullable();
            $table->decimal('CollectionEdit', 18, 2)->nullable();
            $table->text('CollectionEditWords')->nullable();
            $table->decimal('EndingEdit', 18, 2)->nullable();
            $table->text('EndingEditWords')->nullable();
            $table->decimal('DepositEdit', 18, 2)->nullable();
            $table->text('DepositEditWords')->nullable();
            $table->decimal('PaymentEdit', 18, 2)->nullable();
            $table->text('PaymentEditWords')->nullable();
            $table->decimal('ComputedBalance', 18, 2)->nullable()->default(0);
            $table->integer('TxnDetailID')->nullable();
            $table->decimal('CollectionDiscrepancy', 18, 2)->nullable();
            $table->decimal('DepositDiscrepancy', 18, 2)->nullable();
            $table->decimal('PaymentDiscrepancy', 18, 2)->nullable();
            $table->text('CollectionRemarks')->nullable();
            $table->text('DepositRemarks')->nullable();
            $table->text('PaymentRemarks')->nullable();
            $table->decimal('BeginningDiscrepancy', 18, 2)->nullable();
            $table->text('BeginningRemarks')->nullable();
            $table->decimal('EndingDiscrepancy', 18, 2)->nullable();
            $table->text('EndingBalanceRemarks')->nullable();
        });

        // 8. cms_cash_record_attachments
        Schema::create('cms_cash_record_attachments', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CohID')->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('OriginalName', 255)->nullable();
            $table->string('SavedName', 255)->nullable();
            $table->string('FilePath', 500)->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

        // 9. cms_cash_record_denomination
        Schema::create('cms_cash_record_denomination', function (Blueprint $table) {
            $table->increments('RecID');
            $table->decimal('Denomination', 18, 2)->nullable();
            $table->string('DenominationWords', 100)->nullable();
            $table->integer('Count')->nullable();
            $table->decimal('Cash', 18, 2)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->integer('CohID')->nullable();
        });

        // 10. cms_cash_record_deposit
        Schema::create('cms_cash_record_deposit', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('BankD', 100)->nullable();
            $table->decimal('AmountD', 18, 2)->nullable();
            $table->string('FolderNameD', 255)->nullable();
            $table->string('FileNameD', 255)->nullable();
            $table->string('DisplayNameD', 255)->nullable();
            $table->string('FileExtensionD', 20)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->integer('CohID')->nullable();
        });

        // 11. cms_cash_record_payment
        Schema::create('cms_cash_record_payment', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('ParticularP', 255)->nullable();
            $table->string('ReferenceP', 100)->nullable();
            $table->decimal('AmountP', 18, 2)->nullable();
            $table->string('FolderNameP', 255)->nullable();
            $table->string('FileNameP', 255)->nullable();
            $table->string('DisplayNameP', 255)->nullable();
            $table->string('FileExtensionP', 20)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->integer('CohID')->nullable();
        });

        // 12. cms_cash_record_series
        Schema::create('cms_cash_record_series', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CohID')->nullable();
            $table->string('DocumentType', 50)->nullable();
            $table->string('LastSeriesNo', 50)->nullable()->default('');
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

        // 13. cms_cash_record_withdrawal
        Schema::create('cms_cash_record_withdrawal', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('BankW', 100)->nullable();
            $table->decimal('AmountW', 18, 2)->nullable();
            $table->string('FolderNameW', 255)->nullable();
            $table->string('FileNameW', 255)->nullable();
            $table->string('DisplayNameW', 255)->nullable();
            $table->string('FileExtensionW', 20)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->integer('CohID')->nullable();
        });

        // 14. cms_cash_accountability
        Schema::create('cms_cash_accountability', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('RecID')->nullable();
            $table->decimal('TotalDenomination', 18, 2)->nullable()->default(0);
            $table->text('Denominations')->nullable();
            $table->text('SeriesNumbers')->nullable();
            $table->text('AttachmentSummary')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

        // 15. cms_cashaccount_details
        Schema::create('cms_cashaccount_details', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Journal', 50)->nullable();
            $table->string('BranchID', 50)->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->string('CashAccountDesc', 255)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->decimal('Credit', 18, 2)->nullable();
            $table->decimal('Debit', 18, 2)->nullable();
            $table->decimal('Amount', 18, 2)->nullable();
            $table->string('Recieept', 100)->nullable();
            $table->string('DocumentRef', 100)->nullable();
            $table->string('PostPeriod', 20)->nullable();
            $table->string('Module', 50)->nullable();
            $table->string('ReferenceNumber', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('Posted', 10)->nullable();
            $table->string('Released', 10)->nullable();
            $table->string('Status', 20)->nullable();
            $table->decimal('TransactionAmount', 18, 2)->nullable();
            $table->date('TransactionDate')->nullable();
            $table->text('TransactionDesc')->nullable();
            $table->string('DocumentNumber', 100)->nullable();
            $table->string('Company', 100)->nullable();
            $table->string('DocumentType', 50)->nullable();
            $table->text('Note')->nullable();
            $table->string('BankRef', 100)->nullable();
        });

        // 16. cms_cashaccount_details_rm (same as details minus Note and BankRef)
        Schema::create('cms_cashaccount_details_rm', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Journal', 50)->nullable();
            $table->string('BranchID', 50)->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->string('CashAccountDesc', 255)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->decimal('Credit', 18, 2)->nullable();
            $table->decimal('Debit', 18, 2)->nullable();
            $table->decimal('Amount', 18, 2)->nullable();
            $table->string('Recieept', 100)->nullable();
            $table->string('DocumentRef', 100)->nullable();
            $table->string('PostPeriod', 20)->nullable();
            $table->string('Module', 50)->nullable();
            $table->string('ReferenceNumber', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('Posted', 10)->nullable();
            $table->string('Released', 10)->nullable();
            $table->string('Status', 20)->nullable();
            $table->decimal('TransactionAmount', 18, 2)->nullable();
            $table->date('TransactionDate')->nullable();
            $table->text('TransactionDesc')->nullable();
            $table->string('DocumentNumber', 100)->nullable();
            $table->string('Company', 100)->nullable();
            $table->string('DocumentType', 50)->nullable();
        });

        // 17. cms_cashaccount_unposted (same as details minus Note)
        Schema::create('cms_cashaccount_unposted', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Journal', 50)->nullable();
            $table->string('BranchID', 50)->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->string('CashAccountDesc', 255)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->decimal('Credit', 18, 2)->nullable();
            $table->decimal('Debit', 18, 2)->nullable();
            $table->decimal('Amount', 18, 2)->nullable();
            $table->string('Recieept', 100)->nullable();
            $table->string('DocumentRef', 100)->nullable();
            $table->string('PostPeriod', 20)->nullable();
            $table->string('Module', 50)->nullable();
            $table->string('ReferenceNumber', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('Posted', 10)->nullable();
            $table->string('Released', 10)->nullable();
            $table->string('Status', 20)->nullable();
            $table->decimal('TransactionAmount', 18, 2)->nullable();
            $table->date('TransactionDate')->nullable();
            $table->text('TransactionDesc')->nullable();
            $table->string('DocumentNumber', 100)->nullable();
            $table->string('Company', 100)->nullable();
            $table->string('DocumentType', 50)->nullable();
            $table->string('BankRef', 100)->nullable();
        });

        // 18. cms_cashaccount_deposit
        Schema::create('cms_cashaccount_deposit', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('TranType', 50)->nullable();
            $table->string('ReferenceNbr', 100)->nullable();
            $table->string('DeposittoAccount', 50)->nullable();
            $table->date('DepositDate')->nullable();
            $table->string('DocumentRef', 100)->nullable();
            $table->string('WithdrawfromAccount', 50)->nullable();
            $table->decimal('CashDropAmount', 18, 2)->nullable();
            $table->text('Description')->nullable();
            $table->string('Status', 20)->nullable();
            $table->string('Company', 100)->nullable();
        });

        // 19. cms_cashaccount_fundtransfer
        Schema::create('cms_cashaccount_fundtransfer', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('TransferNumber', 100)->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->date('TransferDate')->nullable();
            $table->string('DestinationAccount', 50)->nullable();
            $table->date('ReceiptDate')->nullable();
            $table->string('TypeofTransaction', 50)->nullable();
            $table->string('Status', 20)->nullable();
            $table->decimal('SourceAmount', 18, 2)->nullable();
            $table->string('Company', 100)->nullable();
        });

        // 20. cms_cashaccount_withdrawal
        Schema::create('cms_cashaccount_withdrawal', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('CashAccount', 50)->nullable();
            $table->decimal('PaymentAmount', 18, 2)->nullable();
            $table->date('ApplicationDate')->nullable();
            $table->date('ClosedDate')->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('ReferenceNbr', 100)->nullable();
            $table->string('Company', 100)->nullable();
            $table->string('Status', 20)->nullable();
        });

        // 21. cms_ft_approval
        Schema::create('cms_ft_approval', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Serial', 100)->nullable();
            $table->string('Reference', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('Company', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->tinyInteger('IsDisapproved')->nullable()->default(0);
            $table->tinyInteger('IsFullyApproved')->nullable()->default(0);
        });

        // 22. cms_ft_disapproved
        Schema::create('cms_ft_disapproved', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Reference', 100)->nullable();
            $table->integer('FTID')->nullable();
            $table->text('Reason')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

        // 23. cms_ft_sequence
        Schema::create('cms_ft_sequence', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Type', 50)->nullable();
            $table->string('Signatory', 100)->nullable();
            $table->integer('PositionSequence')->nullable();
            $table->integer('Sequence')->nullable();
            $table->tinyInteger('IsView')->nullable()->default(0);
            $table->string('Status', 20)->nullable();
            $table->dateTime('ApprovedDate')->nullable();
            $table->integer('FTID')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
        });

        // 24. cms_ft_sequence_steps
        Schema::create('cms_ft_sequence_steps', function (Blueprint $table) {
            $table->increments('RecID');
            $table->integer('FTID')->nullable();
            $table->text('Steps')->nullable();
        });

        // 25. cms_fund_voucher
        Schema::create('cms_fund_voucher', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('TransferNbr', 100)->nullable();
            $table->string('BatchNbr', 100)->nullable();
            $table->date('BatchDate')->nullable();
            $table->string('BatchFinPeriod', 20)->nullable();
            $table->string('Branch', 50)->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->string('Account', 50)->nullable();
            $table->string('Subaccount', 50)->nullable();
            $table->text('Particulars')->nullable();
            $table->decimal('Debit', 18, 2)->nullable();
            $table->decimal('Credit', 18, 2)->nullable();
            $table->string('ExternalReference', 100)->nullable();
            $table->text('Purpose')->nullable();
            $table->text('Remarks')->nullable();
            $table->string('FirstName', 100)->nullable();
            $table->string('LastName', 100)->nullable();
            $table->string('Company', 100)->nullable();
            $table->integer('LineNbr')->nullable();
            $table->decimal('TotalDebit', 18, 2)->nullable();
            $table->decimal('TotalCredit', 18, 2)->nullable();
            $table->string('Currency', 10)->nullable();
        });

        // 26. cms_multiple_notification
        Schema::create('cms_multiple_notification', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('MultipleID', 50)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('ApprovedBy', 100)->nullable();
            $table->tinyInteger('is_read')->nullable()->default(0);
            $table->integer('user_id')->nullable()->default(0);
        });

        // 27. cms_multiple_recon
        Schema::create('cms_multiple_recon', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('MultipleID', 50)->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('BankRef', 100)->nullable();
            $table->string('AcumaticaRef', 100)->nullable();
            $table->string('Company', 100)->nullable();
            $table->decimal('Amount', 18, 2)->nullable();
            $table->decimal('Remaining', 18, 2)->nullable();
            $table->decimal('Remain', 18, 2)->nullable();
            $table->string('Type', 50)->nullable();
            $table->tinyInteger('Status')->nullable();
            $table->string('Approver', 100)->nullable();
            $table->dateTime('ApprovedAt')->nullable();
            $table->text('Remarks')->nullable();
        });

        // 28. cms_note_logs
        Schema::create('cms_note_logs', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Maker', 100)->nullable();
            $table->dateTime('EditDate')->nullable();
            $table->text('Note')->nullable();
            $table->string('TransactionID', 100)->nullable();
        });

        // 29. cms_notification_counter
        Schema::create('cms_notification_counter', function (Blueprint $table) {
            $table->integer('user_id')->primary();
            $table->integer('unread_count')->default(0);
            $table->dateTime('last_updated')->nullable()->useCurrent();
        });

        // 30. cms_rtof_list
        Schema::create('cms_rtof_list', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Series', 50)->nullable();
            $table->string('Company', 100)->nullable();
            $table->date('DateFrom')->nullable();
            $table->date('DateTo')->nullable();
            $table->string('Source', 100)->nullable();
            $table->string('SourceName', 255)->nullable();
            $table->text('SourceDescription')->nullable();
            $table->string('Receipt', 100)->nullable();
            $table->string('ReceiptName', 255)->nullable();
            $table->text('ReceiptDescription')->nullable();
            $table->text('Particulars')->nullable();
            $table->decimal('Amount', 18, 2)->nullable();
            $table->text('AmountInWords')->nullable();
            $table->string('File1', 500)->nullable();
            $table->string('File2', 500)->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('SourceCashacc', 50)->nullable();
            $table->string('ReceiptCashacc', 50)->nullable();
        });

        // 31. cms_statement_of_account_returned
        Schema::create('cms_statement_of_account_returned', function (Blueprint $table) {
            $table->increments('RecID');
            $table->integer('SOAID')->nullable();
            $table->text('SOA')->nullable();
            $table->text('Remarks')->nullable();
            $table->decimal('PassbookBal', 18, 2)->nullable();
            $table->string('TransacBy', 100)->nullable();
            $table->dateTime('TransacAt')->nullable();
            $table->string('AccountNo', 50)->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('Status', 20)->nullable();
            $table->string('Approved', 20)->nullable();
            $table->tinyInteger('IsExcel')->nullable();
            $table->string('Approver', 100)->nullable();
            $table->string('Attachment', 500)->nullable();
        });

        // 32. cms_unbind_logs
        Schema::create('cms_unbind_logs', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Maker', 100)->nullable();
            $table->dateTime('UnbindDate')->nullable();
            $table->string('Reference', 100)->nullable();
            $table->string('TransactionID', 100)->nullable();
        });

        // 33. cms_voucher_details
        Schema::create('cms_voucher_details', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Vendor', 100)->nullable();
            $table->string('VendorName', 255)->nullable();
            $table->string('ReferenceNbr', 100)->nullable();
            $table->string('AddressLine1', 255)->nullable();
            $table->string('AddressLine2', 255)->nullable();
            $table->string('AddressLine3', 255)->nullable();
            $table->string('AddressLine4', 255)->nullable();
            $table->string('AddressLine5', 255)->nullable();
            $table->string('AddressLine6', 255)->nullable();
            $table->string('AddressLine7', 255)->nullable();
            $table->string('AddressLine8', 255)->nullable();
            $table->date('PostDate')->nullable();
            $table->string('Currency', 10)->nullable();
            $table->string('CheckNo', 50)->nullable();
            $table->string('APAccount', 50)->nullable();
            $table->string('APDesc', 255)->nullable();
            $table->string('APSub', 50)->nullable();
            $table->decimal('APPayment', 18, 2)->nullable();
            $table->string('CHAccount', 50)->nullable();
            $table->string('CHDesc', 255)->nullable();
            $table->string('CHSub', 50)->nullable();
            $table->decimal('CHPayment', 18, 2)->nullable();
            $table->string('BLType', 50)->nullable();
            $table->string('BLRef', 100)->nullable();
            $table->date('BLDate')->nullable();
            $table->text('BLDesc')->nullable();
            $table->decimal('BLAmount', 18, 2)->nullable();
            $table->decimal('BLWitholding', 18, 2)->nullable();
            $table->text('Purpose')->nullable();
            $table->string('Name', 255)->nullable();
            $table->string('Company', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('Phone1', 20)->nullable();
            $table->string('Phone2', 20)->nullable();
            $table->string('TIN', 20)->nullable();
            $table->string('ECPF', 100)->nullable();
            $table->string('DPEAF', 100)->nullable();
            $table->text('EPurpose')->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->string('CADescription', 255)->nullable();
            $table->date('EDate')->nullable();
            $table->decimal('Total', 18, 2)->nullable();
            $table->tinyInteger('IsPrint')->nullable();
            $table->string('Approver', 100)->nullable();
        });

        // 34. cms_voucher_ft_details
        Schema::create('cms_voucher_ft_details', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('TransferNbr', 100)->nullable();
            $table->string('BatchNbr', 100)->nullable();
            $table->date('TransferDate')->nullable();
            $table->string('FinPeriod', 20)->nullable();
            $table->string('SOBranch', 50)->nullable();
            $table->string('SOAccount', 50)->nullable();
            $table->string('SOName', 255)->nullable();
            $table->string('SOSubaccount', 50)->nullable();
            $table->text('SOParticulars')->nullable();
            $table->decimal('SOAmount', 18, 2)->nullable();
            $table->string('DABranch', 50)->nullable();
            $table->string('DAAccount', 50)->nullable();
            $table->string('DAName', 255)->nullable();
            $table->string('DASubaccount', 50)->nullable();
            $table->text('DAParticulars')->nullable();
            $table->decimal('DAAmount', 18, 2)->nullable();
            $table->string('DocRef', 100)->nullable();
            $table->text('Description')->nullable();
            $table->string('NoteID', 100)->nullable();
            $table->string('Name', 255)->nullable();
            $table->string('Company', 100)->nullable();
            $table->string('ECPF', 100)->nullable();
            $table->string('DPEAF', 100)->nullable();
            $table->text('EPurpose')->nullable();
            $table->string('CashAccount', 50)->nullable();
            $table->string('CADescription', 255)->nullable();
            $table->date('EDate')->nullable();
            $table->tinyInteger('IsPrint')->nullable();
        });

        // 35. cms_voucher_logs
        Schema::create('cms_voucher_logs', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Reference', 100)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('CreatedBy', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('Company', 100)->nullable();
        });

        // 36. cms_bills_payment
        Schema::create('cms_bills_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_id', 50)->nullable();
            $table->text('json_data')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        // 37. cms_bills_payment_saved
        Schema::create('cms_bills_payment_saved', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account_number', 50)->nullable();
            $table->decimal('amount', 18, 2)->nullable();
            $table->dateTime('txn_date_time')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('account_name', 255)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('check_no', 50)->nullable();
            $table->date('check_date')->nullable();
            $table->string('check_type', 50)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->string('product_code', 50)->nullable();
            $table->string('channel_name', 100)->nullable();
            $table->string('branch_code', 20)->nullable();
            $table->string('teller_id', 50)->nullable();
            $table->string('late_check', 10)->nullable();
            $table->string('biller_code', 50)->nullable();
            $table->string('institution_code', 50)->nullable();
            $table->string('institution_name', 255)->nullable();
            $table->text('additional_field')->nullable();
            $table->string('secret_key', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->text('others')->nullable();
        });

        // 38. cms_bills_payment_validation
        Schema::create('cms_bills_payment_validation', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_number', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->decimal('amount', 18, 2)->nullable();
            $table->dateTime('txn_date_time')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('account_name', 255)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('product_code', 50)->nullable();
            $table->string('channel_name', 100)->nullable();
            $table->string('institution_code', 50)->nullable();
            $table->string('biller_code', 50)->nullable();
            $table->text('additional_field')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->string('secret_key', 255)->nullable();
            $table->text('others')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_bills_payment_validation');
        Schema::dropIfExists('cms_bills_payment_saved');
        Schema::dropIfExists('cms_bills_payment');
        Schema::dropIfExists('cms_voucher_logs');
        Schema::dropIfExists('cms_voucher_ft_details');
        Schema::dropIfExists('cms_voucher_details');
        Schema::dropIfExists('cms_unbind_logs');
        Schema::dropIfExists('cms_statement_of_account_returned');
        Schema::dropIfExists('cms_rtof_list');
        Schema::dropIfExists('cms_notification_counter');
        Schema::dropIfExists('cms_note_logs');
        Schema::dropIfExists('cms_multiple_recon');
        Schema::dropIfExists('cms_multiple_notification');
        Schema::dropIfExists('cms_fund_voucher');
        Schema::dropIfExists('cms_ft_sequence_steps');
        Schema::dropIfExists('cms_ft_sequence');
        Schema::dropIfExists('cms_ft_disapproved');
        Schema::dropIfExists('cms_ft_approval');
        Schema::dropIfExists('cms_cashaccount_withdrawal');
        Schema::dropIfExists('cms_cashaccount_fundtransfer');
        Schema::dropIfExists('cms_cashaccount_deposit');
        Schema::dropIfExists('cms_cashaccount_unposted');
        Schema::dropIfExists('cms_cashaccount_details_rm');
        Schema::dropIfExists('cms_cashaccount_details');
        Schema::dropIfExists('cms_cash_accountability');
        Schema::dropIfExists('cms_cash_record_withdrawal');
        Schema::dropIfExists('cms_cash_record_series');
        Schema::dropIfExists('cms_cash_record_payment');
        Schema::dropIfExists('cms_cash_record_deposit');
        Schema::dropIfExists('cms_cash_record_denomination');
        Schema::dropIfExists('cms_cash_record_attachments');
        Schema::dropIfExists('cms_cash_record_main');
        Schema::dropIfExists('cms_cash_record_txn_details');
        Schema::dropIfExists('cms_cash_position');
        Schema::dropIfExists('cms_branch_tagging');
        Schema::dropIfExists('cms_approval_matrix');
        Schema::dropIfExists('cms_bank_code');
        Schema::dropIfExists('tblbranch');
    }
};
