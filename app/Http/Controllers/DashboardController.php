<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ScheduledTraining::class);

        $today = Date::now();
        $scheduledTrainings = $request->user()
            ->scheduledTrainings()
            ->select([
                'id',
                'trainee_id',
                'training_group_id',
                'starts_at',
                'ends_at',
                'location',
                'status',
            ])
            ->with(['trainee:id,name', 'trainingGroup:id,name'])
            ->whereBetween('starts_at', [$today->startOfDay(), $today->endOfDay()])
            ->orderBy('starts_at')
            ->get()
            ->map($this->toDashboardItem(...));

        return Inertia::render('dashboard', [
            'scheduledTrainings' => $scheduledTrainings,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     starts_at: string,
     *     ends_at: string,
     *     subject_name: string,
     *     subject_type: 'trainee'|'training_group',
     *     location: string,
     *     status: string
     * }
     */
    private function toDashboardItem(ScheduledTraining $scheduledTraining): array
    {
        $isTraineeTraining = $scheduledTraining->trainee_id !== null;
        $subjectName = $isTraineeTraining
            ? $scheduledTraining->trainee->name
            : $scheduledTraining->trainingGroup->name;

        return [
            'id' => $scheduledTraining->id,
            'starts_at' => $scheduledTraining->starts_at->toIso8601String(),
            'ends_at' => $scheduledTraining->ends_at->toIso8601String(),
            'subject_name' => $subjectName,
            'subject_type' => $isTraineeTraining ? 'trainee' : 'training_group',
            'location' => $scheduledTraining->location,
            'status' => $scheduledTraining->status,
        ];
    }
}
