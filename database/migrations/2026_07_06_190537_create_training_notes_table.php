<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('training_plan_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('trainee_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('intensity');
            $table->string('result');
            $table->json('tags')->default('[]');
            $table->text('note');
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE training_notes
                ADD CONSTRAINT training_notes_subject_check
                    CHECK (
                        (trainee_id IS NOT NULL AND training_group_id IS NULL)
                        OR (trainee_id IS NULL AND training_group_id IS NOT NULL)
                    ),
                ADD CONSTRAINT training_notes_intensity_check
                    CHECK (intensity IN ('low', 'medium', 'high')),
                ADD CONSTRAINT training_notes_result_check
                    CHECK (result IN ('bad', 'normal', 'good'))
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_notes');
    }
};
