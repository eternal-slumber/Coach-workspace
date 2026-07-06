<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'sport_type', 'age_range', 'level', 'goal', 'restrictions', 'notes'])]
class TrainingGroup extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ScheduledTraining, $this>
     */
    public function scheduledTrainings(): HasMany
    {
        return $this->hasMany(ScheduledTraining::class);
    }
}
