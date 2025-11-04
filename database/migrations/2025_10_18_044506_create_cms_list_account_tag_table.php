<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_list_account_tag', function (Blueprint $table) {
            $table->id('RecID');
            $table->string('AccountTag', 200);
            $table->string('Abbreviation', 50)->nullable();
            $table->integer('CreatedBy')->default(1);
            $table->timestamp('CreatedAt')->useCurrent();
            $table->integer('Status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_list_account_tag');
    }
};
