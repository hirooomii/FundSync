<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_cash_account', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Company', 550)->nullable();
            $table->string('Branch', 550)->nullable();
            $table->string('BranchID', 550)->nullable();
            $table->string('CashAccount', 550)->nullable();
            $table->string('Description', 550)->nullable();
            $table->integer('IsActive')->nullable();
            $table->string('AccountNo', 550)->nullable();
            $table->string('CreatedBy', 550)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });

         Schema::create('cms_bank_depository', function (Blueprint $table) {
            $table->increments('RecID'); 
            $table->string('Company', 550)->nullable();
            $table->string('BankName', 550)->nullable();
            $table->string('AccountNo', 550)->nullable();
            $table->string('AccountName', 550)->nullable();
            $table->string('DepositType', 550)->nullable();
            $table->string('Description', 550)->nullable();
            $table->string('AccountTag', 550)->nullable();
            $table->float('InterestRate')->nullable();
            $table->float('BeginningBal')->nullable();
            $table->float('MaintainingBal')->nullable();
            $table->string('BankBranch', 550)->nullable();
            $table->string('BankStreet', 550)->nullable();
            $table->string('BankCity', 550)->nullable();
            $table->string('BankProvince', 550)->nullable();
            $table->dateTime('DateOpen')->nullable();
            $table->dateTime('MaturityDate')->nullable();
            $table->string('DepContactNum', 550)->nullable();
            $table->string('DepContactPerson', 550)->nullable();
            $table->string('DepEmailAdd', 550)->nullable();
            $table->string('DepPosition', 550)->nullable();
            $table->string('Status', 550)->nullable();
            $table->string('CreatedBy', 550)->nullable();
            $table->dateTime('CreatedAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_cash_account');
        Schema::dropIfExists('cms_bank_depository');
    }
};
