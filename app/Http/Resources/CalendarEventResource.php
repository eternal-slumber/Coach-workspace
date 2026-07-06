<?php

namespace App\Http\Resources;

use App\Models\ScheduledTraining;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ScheduledTraining */
class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isTraineeTraining = $this->trainee_id !== null;

        return [
            'id' => $this->id,
            'title' => $isTraineeTraining
                ? $this->trainee->name
                : $this->trainingGroup->name,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'location' => $this->location,
            'status' => $this->status,
            'color' => $this->color,
            'notes' => $this->notes,
            'trainee_id' => $this->trainee_id,
            'training_group_id' => $this->training_group_id,
            'subject_type' => $isTraineeTraining ? 'trainee' : 'training_group',
        ];
    }
}
