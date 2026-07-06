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
        Schema::create('scheduled_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainee_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->foreignId('training_group_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->string('location');
            $table->string('status')->default('planned');
            $table->timestamps();

            $table->index(['user_id', 'starts_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE scheduled_trainings
                ADD CONSTRAINT scheduled_trainings_subject_check
                    CHECK (
                        (trainee_id IS NOT NULL AND training_group_id IS NULL)
                        OR (trainee_id IS NULL AND training_group_id IS NOT NULL)
                    ),
                ADD CONSTRAINT scheduled_trainings_status_check
                    CHECK (status IN ('planned', 'completed', 'cancelled'))
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_trainings');
    }
};
