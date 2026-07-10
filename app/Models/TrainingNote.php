<?php

namespace App\Models;

use Database\Factories\TrainingNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['intensity', 'result', 'tags', 'note'])]
class TrainingNote extends Model
{
    /** @use HasFactory<TrainingNoteFactory> */
    use HasFactory;

    /** @var list<string> */
    public const INTENSITIES = ['low', 'medium', 'high'];

    /** @var list<string> */
    public const RESULTS = ['bad', 'normal', 'good'];

    /** @var array<string, mixed> */
    protected $attributes = [
        'tags' => '[]',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<TrainingPlan, $this> */
    public function trainingPlan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlan::class);
    }

    /** @return BelongsTo<Trainee, $this> */
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    /** @return BelongsTo<TrainingGroup, $this> */
    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }
}
