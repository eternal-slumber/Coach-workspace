<?php

namespace App\Http\Controllers;

use App\Http\Resources\CalendarEventResource;
use App\Models\ScheduledTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', ScheduledTraining::class);

        $user = $request->user();
        $scheduledTrainings = $user
            ->scheduledTrainings()
            ->with(['trainee:id,name', 'trainingGroup:id,name'])
            ->orderBy('starts_at')
            ->get()
            ->map(
                fn (ScheduledTraining $scheduledTraining): array => (new CalendarEventResource($scheduledTraining))->resolve(),
            );

        return Inertia::render('calendar', [
            'scheduledTrainings' => $scheduledTrainings,
            'workingHours' => [
                'startsAt' => substr($user->working_day_starts_at, 0, 5),
                'endsAt' => substr($user->working_day_ends_at, 0, 5),
            ],
        ]);
    }
}
