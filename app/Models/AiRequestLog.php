<?php

namespace App\Models;

use Database\Factories\AiRequestLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequestLog extends Model
{
    /** @use HasFactory<AiRequestLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'status',
        'prompt_preview',
        'response_preview',
        'error_message',
        'duration_ms',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }
}
