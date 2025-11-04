<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_position', function (Blueprint $table) {
            $table->increments('RecID');
            $table->integer('POSITIONCODE');
            $table->string('POSITIONDESC', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_position');
    }
};
