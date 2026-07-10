<?php

namespace App\Http\Requests;

use App\Models\TrainingPlan;
use Illuminate\Validation\Validator;

class StoreTrainingNoteRequest extends TrainingNoteRequest
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
        return $this->noteRules();
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $trainingPlan = $this->route('training_plan');

            if (! $trainingPlan instanceof TrainingPlan) {
                return;
            }

            if ($trainingPlan->status !== 'completed') {
                $validator->errors()->add(
                    'training_plan',
                    __('A note can only be added to a completed training plan.'),
                );
            }

            if ($trainingPlan->trainingNote()->exists()) {
                $validator->errors()->add(
                    'training_plan',
                    __('This training plan already has a note.'),
                );
            }
        }];
    }
}
