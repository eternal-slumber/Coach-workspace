<?php

namespace App\Models;

use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'description',
    'goal',
    'difficulty',
    'equipment',
    'duration_minutes',
    'contraindications',
    'age_min',
    'age_max',
    'tags',
])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            'is_system' => 'boolean',
        ];
    }
}
