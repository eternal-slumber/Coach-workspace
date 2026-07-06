<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTraineeRequest;
use App\Http\Requests\UpdateTraineeRequest;
use App\Models\Trainee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TraineeController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Trainee::class);

        return Inertia::render('trainees/index', [
            'trainees' => $request->user()
                ->trainees()
                ->select(['id', 'name', 'age', 'level', 'goal'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Trainee::class);

        return Inertia::render('trainees/create');
    }

    public function store(StoreTraineeRequest $request): RedirectResponse
    {
        $trainee = $request->user()->trainees()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee created.')]);

        return to_route('trainees.show', $trainee);
    }

    public function show(Trainee $trainee): Response
    {
        Gate::authorize('view', $trainee);

        return Inertia::render('trainees/show', [
            'trainee' => $trainee->only([
                'id',
                'name',
                'age',
                'level',
                'goal',
                'restrictions',
                'notes',
            ]),
        ]);
    }

    public function edit(Trainee $trainee): Response
    {
        Gate::authorize('update', $trainee);

        return Inertia::render('trainees/edit', [
            'trainee' => $trainee->only([
                'id',
                'name',
                'age',
                'level',
                'goal',
                'restrictions',
                'notes',
            ]),
        ]);
    }

    public function update(UpdateTraineeRequest $request, Trainee $trainee): RedirectResponse
    {
        $trainee->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee updated.')]);

        return to_route('trainees.show', $trainee);
    }

    public function destroy(Trainee $trainee): RedirectResponse
    {
        Gate::authorize('delete', $trainee);

        $trainee->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee deleted.')]);

        return to_route('trainees.index');
    }
}
