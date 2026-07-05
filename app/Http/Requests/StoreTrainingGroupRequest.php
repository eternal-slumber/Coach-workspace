<?php

namespace App\Http\Requests;

use App\Models\TrainingGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TrainingGroup::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sport_type' => ['required', 'string', 'max:255'],
            'age_range' => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'max:100'],
            'goal' => ['required', 'string', 'max:5000'],
            'restrictions' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
