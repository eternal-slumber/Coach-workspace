<?php

namespace App\Http\Requests;

use App\Models\TrainingNote;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class TrainingNoteRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function noteRules(): array
    {
        return [
            'intensity' => ['required', Rule::in(TrainingNote::INTENSITIES)],
            'result' => ['required', Rule::in(TrainingNote::RESULTS)],
            'tags' => ['array', 'max:20'],
            'tags.*' => ['string', 'max:100', 'distinct'],
            'note' => ['required', 'string', 'max:20000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tags' => $this->parseTags($this->input('tags')),
        ]);
    }

    /** @return list<string> */
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
