<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExerciseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Exercise::class) ?? false;
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
            'description' => ['required', 'string', 'max:10000'],
            'goal' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', Rule::in(['Лёгкая', 'Средняя', 'Высокая'])],
            'equipment' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'between:1,480'],
            'contraindications' => ['nullable', 'string', 'max:5000'],
            'age_min' => ['nullable', 'integer', 'between:1,120'],
            'age_max' => ['nullable', 'integer', 'between:1,120', 'gte:age_min'],
            'tags' => ['array', 'max:20'],
            'tags.*' => ['string', 'max:50', 'distinct'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tags' => $this->parseTags($this->input('tags')),
        ]);
    }

    /** @return list<mixed> */
    private function parseTags(mixed $tags): array
    {
        if (is_array($tags)) {
            return $tags;
        }

        if (! is_string($tags)) {
            return [];
        }

        return collect(explode(',', $tags))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
