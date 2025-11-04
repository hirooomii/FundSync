<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_bank_signatories', function (Blueprint $table) {
            $table->id('RecID');
            $table->integer('EmployeeID')->nullable();
            $table->string('Name', 550)->nullable();
            $table->string('Position', 550)->nullable();
            $table->string('AccountNo', 550)->nullable();
            $table->string('CreatedBy', 550)->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->integer('Status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_bank_signatories');
    }
};
