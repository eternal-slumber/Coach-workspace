<?php

namespace App\Http\Controllers;

use App\Http\Requests\RescheduleScheduledTrainingRequest;
use App\Models\ScheduledTraining;
use Illuminate\Http\JsonResponse;

class RescheduleScheduledTrainingController extends Controller
{
    public function __invoke(
        RescheduleScheduledTrainingRequest $request,
        ScheduledTraining $scheduledTraining,
    ): JsonResponse {
        $scheduledTraining->update($request->validated());

        return response()->json([
            'starts_at' => $scheduledTraining->starts_at->toIso8601String(),
            'ends_at' => $scheduledTraining->ends_at->toIso8601String(),
        ]);
    }
}
