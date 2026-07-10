<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingNoteRequest;
use App\Http\Requests\UpdateTrainingNoteRequest;
use App\Models\TrainingNote;
use App\Models\TrainingPlan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TrainingNoteController extends Controller
{
    public function store(
        StoreTrainingNoteRequest $request,
        TrainingPlan $trainingPlan,
    ): RedirectResponse {
        $trainingNote = new TrainingNote($request->validated());
        $trainingNote->forceFill([
            'user_id' => $request->user()->id,
            'training_plan_id' => $trainingPlan->id,
            'trainee_id' => $trainingPlan->trainee_id,
            'training_group_id' => $trainingPlan->training_group_id,
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Training note created.')]);

        return to_route('training-plans.show', $trainingPlan);
    }

    public function update(
        UpdateTrainingNoteRequest $request,
        TrainingNote $trainingNote,
    ): RedirectResponse {
        $trainingNote->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Training note updated.')]);

        return to_route('training-plans.show', $trainingNote->trainingPlan);
    }
}
