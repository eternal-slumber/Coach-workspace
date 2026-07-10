<?php

namespace App\Actions\TrainingPlans;

use App\Models\TrainingPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CompleteTrainingPlanAction
{
    public function execute(User $user, TrainingPlan $trainingPlan): void
    {
        Gate::forUser($user)->authorize('complete', $trainingPlan);

        DB::transaction(function () use ($trainingPlan): void {
            $lockedTrainingPlan = TrainingPlan::query()
                ->lockForUpdate()
                ->findOrFail($trainingPlan->id);

            $lockedTrainingPlan->update(['status' => 'completed']);
            $lockedTrainingPlan->scheduledTraining()->update(['status' => 'completed']);
        });
    }
}
