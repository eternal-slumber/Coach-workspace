<?php

namespace App\Models;

use Database\Factories\TrainingPlanExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exercise_id',
    'name',
    'description',
    'duration_minutes',
    'sets',
    'repetitions',
    'rest_seconds',
    'position',
    'notes',
])]
class TrainingPlanExercise extends Model
{
    /** @use HasFactory<TrainingPlanExerciseFactory> */
    use HasFactory;

    /** @return BelongsTo<TrainingPlanBlock, $this> */
    public function trainingPlanBlock(): BelongsTo
    {
        return $this->belongsTo(TrainingPlanBlock::class);
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'sets' => 'integer',
            'rest_seconds' => 'integer',
            'position' => 'integer',
        ];
    }
}
