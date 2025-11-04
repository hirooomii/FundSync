<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_statement_of_account', function (Blueprint $table) {
            $table->id('RecID');
            $table->string('SOA', 550)->nullable();
            $table->string('Remarks', 550)->nullable();
            $table->float('PassbookBal')->nullable();
            $table->integer('TransacBy')->nullable();
            $table->dateTime('TransacAt')->nullable();
            $table->string('AccountNo', 550)->nullable();
            $table->string('CreatedBy', 550)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->string('Status', 150)->nullable();
            $table->dateTime('Approved')->nullable();
            $table->integer('IsExcel')->nullable();
            $table->integer('Approver')->nullable();
            $table->integer('Attachment')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cms_statement_of_account');
    }
};
