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
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('scheduled_training_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('trainee_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('goal');
            $table->unsignedSmallInteger('total_duration_minutes');
            $table->string('status')->default('draft')->index();
            $table->string('source')->default('manual')->index();
            $table->text('notes')->nullable();
            $table->json('warnings')->default('[]');
            $table->text('ai_reasoning')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE training_plans
                ADD CONSTRAINT training_plans_subject_check
                    CHECK (
                        (trainee_id IS NOT NULL AND training_group_id IS NULL)
                        OR (trainee_id IS NULL AND training_group_id IS NOT NULL)
                    ),
                ADD CONSTRAINT training_plans_status_check
                    CHECK (status IN ('draft', 'approved', 'completed', 'generating', 'failed')),
                ADD CONSTRAINT training_plans_source_check
                    CHECK (source IN ('manual', 'ai'))
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_plans');
    }
};
