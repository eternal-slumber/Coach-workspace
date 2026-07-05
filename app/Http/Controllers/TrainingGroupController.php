<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingGroupRequest;
use App\Http\Requests\UpdateTrainingGroupRequest;
use App\Models\TrainingGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TrainingGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', TrainingGroup::class);

        return response()->json([
            'training_groups' => $request->user()->trainingGroups()->latest()->get(),
        ]);
    }

    public function store(StoreTrainingGroupRequest $request): JsonResponse
    {
        $trainingGroup = $request->user()->trainingGroups()->create($request->validated());

        return response()->json($trainingGroup, 201);
    }

    public function show(TrainingGroup $trainingGroup): JsonResponse
    {
        Gate::authorize('view', $trainingGroup);

        return response()->json($trainingGroup);
    }

    public function update(UpdateTrainingGroupRequest $request, TrainingGroup $trainingGroup): JsonResponse
    {
        $trainingGroup->update($request->validated());

        return response()->json($trainingGroup);
    }

    public function destroy(TrainingGroup $trainingGroup): JsonResponse
    {
        Gate::authorize('delete', $trainingGroup);

        $trainingGroup->delete();

        return response()->json(status: 204);
    }
}
