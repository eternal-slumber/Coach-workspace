<?php

namespace App\Models;

use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'goal',
    'difficulty',
    'equipment',
    'duration_minutes',
    'muscle_groups',
    'load_type',
    'movement_pattern',
    'contraindications',
    'age_min',
    'age_max',
    'tags',
])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    /** @var list<string> */
    public const LOAD_TYPES = [
        'warmup',
        'strength',
        'mobility',
        'coordination',
        'cardio',
        'recovery',
        'game',
    ];

    /** @var list<string> */
    public const MOVEMENT_PATTERNS = [
        'squat',
        'lunge',
        'hinge',
        'push',
        'pull',
        'core',
        'balance',
        'run',
        'jump',
        'stretch',
        'breathing',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<TrainingPlanExercise, $this> */
    public function trainingPlanExercises(): HasMany
    {
        return $this->hasMany(TrainingPlanExercise::class);
    }

    /**
     * @param  Builder<Exercise>  $query
     * @return Builder<Exercise>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $visibleExercises) use ($user): void {
            $visibleExercises
                ->where(function (Builder $systemExercises): void {
                    $systemExercises->where('is_system', true)->whereNull('user_id');
                })
                ->orWhere('user_id', $user->id);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'age_min' => 'integer',
            'age_max' => 'integer',
            'tags' => 'array',
            'muscle_groups' => 'array',
            'is_system' => 'boolean',
        ];
    }
}
