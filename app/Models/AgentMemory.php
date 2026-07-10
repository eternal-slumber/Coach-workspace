<?php

namespace App\Models;

use Database\Factories\AgentMemoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'content', 'importance', 'is_active'])]
class AgentMemory extends Model
{
    /** @use HasFactory<AgentMemoryFactory> */
    use HasFactory;

    /** @var list<string> */
    public const TYPES = [
        'restriction',
        'preference',
        'progress',
        'risk',
        'methodic_note',
        'general',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'importance' => 5,
        'is_active' => true,
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            'importance' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
