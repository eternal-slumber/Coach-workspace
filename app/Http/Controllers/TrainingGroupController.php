<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingGroupRequest;
use App\Http\Requests\UpdateTrainingGroupRequest;
use App\Models\TrainingGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
