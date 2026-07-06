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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('goal');
            $table->string('difficulty')->index();
            $table->string('equipment')->nullable()->index();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('contraindications')->nullable();
            $table->unsignedSmallInteger('age_min')->nullable();
            $table->unsignedSmallInteger('age_max')->nullable();
            $table->json('tags')->default('[]');
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
