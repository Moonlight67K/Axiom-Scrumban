<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('schema_name')->unique();
            $table->timestamps();
            
            $table->index('schema_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
};
