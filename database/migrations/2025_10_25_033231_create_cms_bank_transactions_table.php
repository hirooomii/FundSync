<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('account_no', 25);
            $table->string('bank_code', 25)->nullable();
            $table->date('transaction_date');
            $table->float('amount');
            $table->string('debit_or_credit', 250)->nullable();
            $table->string('transaction_type', 125);
            $table->string('description', 255)->nullable();
            $table->string('reference', 50)->nullable();
            $table->string('additional_info', 255)->nullable();
            $table->string('company', 255)->nullable();
            $table->string('docref', 2000)->nullable();
            $table->dateTime('bindAt')->nullable();
            $table->string('bindBy', 225)->nullable();
            $table->float('runningbal')->nullable();
            $table->float('reconciledAmt')->nullable();
            $table->float('remainingAmt')->nullable();
            $table->string('comment', 550)->nullable();
            $table->float('decimal')->nullable();
            $table->string('note', 550)->nullable();
            $table->integer('SOAID')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_bank_transactions');
    }
};
