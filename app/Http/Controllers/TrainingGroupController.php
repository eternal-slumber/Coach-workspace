<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingGroupRequest;
use App\Http\Requests\UpdateTrainingGroupRequest;
use App\Models\TrainingGroup;
use App\Models\TrainingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TrainingGroupController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', TrainingGroup::class);

        return Inertia::render('training-groups/index', [
            'trainingGroups' => $request->user()
                ->trainingGroups()
                ->select(['id', 'name', 'sport_type', 'age_range', 'level', 'goal'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', TrainingGroup::class);

        return Inertia::render('training-groups/create');
    }

    public function store(StoreTrainingGroupRequest $request): RedirectResponse
    {
        $trainingGroup = $request->user()->trainingGroups()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Training group created.')]);

        return to_route('training-groups.show', $trainingGroup);
    }

    public function show(TrainingGroup $trainingGroup): Response
    {
        Gate::authorize('view', $trainingGroup);

        return Inertia::render('training-groups/show', [
            'trainingGroup' => $trainingGroup->only([
                'id',
                'name',
                'sport_type',
                'age_range',
                'level',
                'goal',
                'restrictions',
                'notes',
            ]),
            'trainingHistory' => $this->trainingHistory($trainingGroup),
            'agentMemories' => $trainingGroup->agentMemories()
                ->where('user_id', $trainingGroup->user_id)
                ->select(['id', 'type', 'content', 'importance', 'is_active'])
                ->orderByDesc('is_active')
                ->orderByDesc('importance')
                ->latest()
                ->get(),
        ]);
    }

    public function edit(TrainingGroup $trainingGroup): Response
    {
        Gate::authorize('update', $trainingGroup);

        return Inertia::render('training-groups/edit', [
            'trainingGroup' => $trainingGroup->only([
                'id',
                'name',
                'sport_type',
                'age_range',
                'level',
                'goal',
                'restrictions',
                'notes',
            ]),
        ]);
    }

    public function update(UpdateTrainingGroupRequest $request, TrainingGroup $trainingGroup): RedirectResponse
    {
        $trainingGroup->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Training group updated.')]);

        return to_route('training-groups.show', $trainingGroup);
    }

    public function destroy(TrainingGroup $trainingGroup): RedirectResponse
    {
        Gate::authorize('delete', $trainingGroup);

        $trainingGroup->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Training group deleted.')]);

        return to_route('training-groups.index');
    }

    /** @return Collection<int, array<string, mixed>> */
    private function trainingHistory(TrainingGroup $trainingGroup): Collection
    {
        return $trainingGroup->trainingPlans()
            ->completed()
            ->latestScheduled()
            ->with([
                'scheduledTraining:id,starts_at',
                'trainingNote:id,training_plan_id,intensity,result,tags,note',
            ])
            ->limit(10)
            ->get([
                'id',
                'scheduled_training_id',
                'title',
                'goal',
                'total_duration_minutes',
                'status',
            ])
            ->map(fn (TrainingPlan $trainingPlan): array => [
                'id' => $trainingPlan->id,
                'title' => $trainingPlan->title,
                'goal' => $trainingPlan->goal,
                'total_duration_minutes' => $trainingPlan->total_duration_minutes,
                'status' => $trainingPlan->status,
                'starts_at' => $trainingPlan->scheduledTraining->starts_at->toIso8601String(),
                'training_note' => $trainingPlan->trainingNote?->only([
                    'intensity',
                    'result',
                    'tags',
                    'note',
                ]),
            ]);
    }
}
