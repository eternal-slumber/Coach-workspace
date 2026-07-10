<?php

namespace App\Http\Requests;

use App\Models\TrainingNote;

class UpdateTrainingNoteRequest extends TrainingNoteRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $trainingNote = $this->route('training_note');

        return $trainingNote instanceof TrainingNote
            && ($this->user()?->can('update', $trainingNote) ?? false);
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
}
