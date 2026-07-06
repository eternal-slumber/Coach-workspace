<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTraining;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DuplicateScheduledTrainingController extends Controller
{
    public function __invoke(
        Request $request,
        ScheduledTraining $scheduledTraining,
    ): RedirectResponse {
        Gate::authorize('duplicate', $scheduledTraining);

        $request->user()->scheduledTrainings()->create([
            'trainee_id' => $scheduledTraining->trainee_id,
            'training_group_id' => $scheduledTraining->training_group_id,
            'starts_at' => $scheduledTraining->starts_at->addWeek(),
            'ends_at' => $scheduledTraining->ends_at->addWeek(),
            'location' => $scheduledTraining->location,
            'status' => 'planned',
            'color' => $scheduledTraining->color,
            'notes' => null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Training duplicated for next week.'),
        ]);

        return to_route('calendar');
    }
}
