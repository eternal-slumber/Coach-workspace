<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduledTrainingRequest;
use App\Http\Requests\UpdateScheduledTrainingRequest;
use App\Models\ScheduledTraining;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ScheduledTrainingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ScheduledTraining::class);

        return response()->json([
            'scheduled_trainings' => $request->user()
                ->scheduledTrainings()
                ->with(['trainee:id,name', 'trainingGroup:id,name'])
                ->orderBy('starts_at')
                ->get(),
        ]);
    }

    public function store(StoreScheduledTrainingRequest $request): JsonResponse
    {
        $scheduledTraining = $request->user()
            ->scheduledTrainings()
            ->create($request->validated());

        return response()->json($scheduledTraining->load(['trainee:id,name', 'trainingGroup:id,name']), 201);
    }

    public function show(ScheduledTraining $scheduledTraining): JsonResponse
    {
        Gate::authorize('view', $scheduledTraining);

        return response()->json(
            $scheduledTraining->load(['trainee:id,name', 'trainingGroup:id,name']),
        );
    }

    public function update(
        UpdateScheduledTrainingRequest $request,
        ScheduledTraining $scheduledTraining,
    ): JsonResponse {
        $scheduledTraining->update($request->validated());

        return response()->json(
            $scheduledTraining->load(['trainee:id,name', 'trainingGroup:id,name']),
        );
    }

    public function destroy(ScheduledTraining $scheduledTraining): Response
    {
        Gate::authorize('delete', $scheduledTraining);

        $scheduledTraining->delete();

        return response()->noContent();
    }
}
