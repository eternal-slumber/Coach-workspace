<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_plan_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_block_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('sets')->nullable();
            $table->string('repetitions')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->unsignedSmallInteger('position');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['training_plan_block_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_plan_exercises');
    }
};
