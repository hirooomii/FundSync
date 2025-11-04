<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('cms_list_bank', function (Blueprint $table) {
            $table->increments('RecID');
            $table->string('Bank', 500);
            $table->string('Abbreviation', 100)->nullable();
            $table->integer('CreatedBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->integer('Status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_list_bank');
    }
};
