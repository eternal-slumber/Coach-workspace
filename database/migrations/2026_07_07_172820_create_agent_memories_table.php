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
        Schema::create('agent_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('trainee_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->foreignId('training_group_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->text('content');
            $table->unsignedTinyInteger('importance')->default(5)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['user_id', 'is_active', 'importance']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE agent_memories
                ADD CONSTRAINT agent_memories_subject_check
                    CHECK (
                        (trainee_id IS NOT NULL AND training_group_id IS NULL)
                        OR (trainee_id IS NULL AND training_group_id IS NOT NULL)
                    ),
                ADD CONSTRAINT agent_memories_type_check
                    CHECK (type IN ('restriction', 'preference', 'progress', 'risk', 'methodic_note', 'general')),
                ADD CONSTRAINT agent_memories_importance_check
                    CHECK (importance BETWEEN 1 AND 10)
                SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_memories');
    }
};
