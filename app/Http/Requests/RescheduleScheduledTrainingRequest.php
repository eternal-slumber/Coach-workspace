<?php

namespace App\Http\Requests;

use App\Models\ScheduledTraining;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleScheduledTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $scheduledTraining = $this->route('scheduled_training');

        return $scheduledTraining instanceof ScheduledTraining
            && ($this->user()?->can('update', $scheduledTraining) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }
}
