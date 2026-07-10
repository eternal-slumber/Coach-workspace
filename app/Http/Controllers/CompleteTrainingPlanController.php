<?php

namespace App\Http\Controllers;

use App\Actions\TrainingPlans\CompleteTrainingPlanAction;
use App\Models\TrainingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompleteTrainingPlanController extends Controller
{
    public function __invoke(
        Request $request,
        TrainingPlan $trainingPlan,
        CompleteTrainingPlanAction $completeTrainingPlan,
    ): RedirectResponse {
        $completeTrainingPlan->execute($request->user(), $trainingPlan);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Training marked as completed.')]);

        return to_route('training-plans.show', $trainingPlan);
    }
}
