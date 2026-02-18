<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('column_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('organization_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('sprint_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('position');

            // 🔥 Change this
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('column_id');
            $table->index('organization_id');
            $table->index('sprint_id');
            $table->index(['column_id', 'position']);
        });

        // ❌ REMOVE THIS (Postgres only)
        // DB::statement('CREATE INDEX idx_tasks_metadata ON tasks USING GIN (metadata)');
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};