<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('cms_list_company', function (Blueprint $table) {
            $table->integer('RecID', true);
            $table->string('Company', 255);
            $table->string('Abbreviation', 100)->nullable();
            $table->integer('CreatedBy')->default(1);
            $table->dateTime('CreatedAt')->useCurrent();
            $table->integer('Status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_list_company');
    }
};
