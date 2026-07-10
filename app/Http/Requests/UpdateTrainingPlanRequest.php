<?php

namespace App\Http\Requests;

use App\Models\TrainingPlan;

class UpdateTrainingPlanRequest extends TrainingPlanRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $trainingPlan = $this->route('training_plan');

        return $trainingPlan instanceof TrainingPlan
            && ($this->user()?->can('update', $trainingPlan) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->planRules();
    }

    /** @return list<string> */
    protected function allowedStatuses(): array
    {
        $trainingPlan = $this->route('training_plan');

        return $trainingPlan instanceof TrainingPlan
            && $trainingPlan->status === 'completed'
                ? ['completed']
                : TrainingPlan::EDITABLE_STATUSES;
    }
}
