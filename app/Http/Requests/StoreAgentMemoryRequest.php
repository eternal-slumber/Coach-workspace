<?php

namespace App\Http\Requests;

use App\Models\AgentMemory;
use App\Models\Trainee;
use App\Models\TrainingGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentMemoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AgentMemory::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trainee_id' => [
                'nullable',
                'integer',
                'required_without:training_group_id',
                Rule::prohibitedIf(fn (): bool => $this->filled('training_group_id')),
                Rule::exists(Trainee::class, 'id')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $this->user()?->id)),
            ],
            'training_group_id' => [
                'nullable',
                'integer',
                'required_without:trainee_id',
                Rule::prohibitedIf(fn (): bool => $this->filled('trainee_id')),
                Rule::exists(TrainingGroup::class, 'id')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $this->user()?->id)),
            ],
            'type' => ['required', Rule::in(AgentMemory::TYPES)],
            'content' => ['required', 'string', 'max:10000'],
            'importance' => ['required', 'integer', 'between:1,10'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
