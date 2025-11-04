<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_bank_balances', function (Blueprint $table) {
            $table->id();
            $table->string('account_no', 25);
            $table->string('currency', 5)->nullable();
            $table->float('opening_balance')->nullable();
            $table->float('closing_balance')->nullable();
            $table->float('available_balance')->nullable();
            $table->date('transaction_date')->nullable();
            $table->integer('SOAID')->nullable();
        });

        Schema::create('cms_bank_balances_pending', function (Blueprint $table) {
            $table->id();
            $table->string('account_no', 25);
            $table->string('currency', 5)->nullable();
            $table->float('opening_balance')->nullable();
            $table->float('closing_balance')->nullable();
            $table->float('available_balance')->nullable();
            $table->date('transaction_date')->nullable();
            $table->integer('SOAID')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_bank_balances');
        Schema::dropIfExists('cms_bank_balances_pending');
    }
};
