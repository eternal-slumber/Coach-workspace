<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTraineeRequest;
use App\Http\Requests\UpdateTraineeRequest;
use App\Models\Trainee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TraineeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Trainee::class);

        return response()->json([
            'trainees' => $request->user()->trainees()->latest()->get(),
        ]);
    }

    public function store(StoreTraineeRequest $request): JsonResponse
    {
        $trainee = $request->user()->trainees()->create($request->validated());

        return response()->json($trainee, 201);
    }

    public function show(Trainee $trainee): JsonResponse
    {
        Gate::authorize('view', $trainee);

        return response()->json($trainee);
    }

    public function update(UpdateTraineeRequest $request, Trainee $trainee): JsonResponse
    {
        $trainee->update($request->validated());

        return response()->json($trainee);
    }

    public function destroy(Trainee $trainee): JsonResponse
    {
        Gate::authorize('delete', $trainee);

        $trainee->delete();

        return response()->json(status: 204);
    }
}
