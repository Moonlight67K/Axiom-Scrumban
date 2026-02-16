<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('board_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('position');
            $table->integer('wip_limit')->default(0);
            $table->timestamps();

            $table->index(['board_id', 'position']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('columns');
    }
};
