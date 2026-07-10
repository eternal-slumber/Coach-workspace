<?php

namespace App\Http\Requests;

use App\Models\ScheduledTraining;
use App\Models\TrainingPlan;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class StoreTrainingPlanRequest extends TrainingPlanRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TrainingPlan::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scheduled_training_id' => [
                'required',
                'integer',
                Rule::exists(ScheduledTraining::class, 'id')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $this->user()?->id)),
                Rule::unique(TrainingPlan::class, 'scheduled_training_id'),
            ],
            ...$this->planRules(),
        ];
    }
}
