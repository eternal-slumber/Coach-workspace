<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['trainee_id', 'training_group_id', 'starts_at', 'ends_at', 'location', 'status'])]
class ScheduledTraining extends Model
{
    /**
     * @var list<string>
     */
    public const STATUSES = ['planned', 'completed', 'cancelled'];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'planned',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Trainee, $this>
     */
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    /**
     * @return BelongsTo<TrainingGroup, $this>
     */
    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }
}
