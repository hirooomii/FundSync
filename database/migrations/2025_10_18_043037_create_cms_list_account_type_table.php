<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_list_account_type', function (Blueprint $table) {
            $table->id('RecID');
            $table->string('AccountType', 255);
            $table->string('Abbreviation', 100)->nullable();
            $table->integer('CreatedBy')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->integer('Status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_list_account_type');
    }
};
