<?php

namespace App\Http\Requests;

use App\Models\AgentMemory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentMemoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $agentMemory = $this->route('agent_memory');

        return $agentMemory instanceof AgentMemory
            && ($this->user()?->can('update', $agentMemory) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(AgentMemory::TYPES)],
            'content' => ['required', 'string', 'max:10000'],
            'importance' => ['required', 'integer', 'between:1,10'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
