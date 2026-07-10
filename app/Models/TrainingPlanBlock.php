<?php

namespace App\Models;

use Database\Factories\TrainingPlanBlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'duration_minutes', 'position', 'notes'])]
class TrainingPlanBlock extends Model
{
    /** @use HasFactory<TrainingPlanBlockFactory> */
    use HasFactory;

    /** @return BelongsTo<TrainingPlan, $this> */
    public function trainingPlan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlan::class);
    }

    /** @return HasMany<TrainingPlanExercise, $this> */
    public function exercises(): HasMany
    {
        return $this->hasMany(TrainingPlanExercise::class)->orderBy('position');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'position' => 'integer',
        ];
    }
}
