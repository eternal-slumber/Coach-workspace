<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Exercise::class);

        return Inertia::render('exercises/index', [
            'exercises' => Exercise::query()
                ->visibleTo($request->user())
                ->select([
                    'id',
                    'user_id',
                    'name',
                    'goal',
                    'difficulty',
                    'equipment',
                    'duration_minutes',
                    'tags',
                    'is_system',
                ])
                ->orderByDesc('is_system')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        Gate::authorize('create', Exercise::class);

        return Inertia::render('exercises/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $exercise = $request->user()->exercises()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise created.')]);

        return to_route('exercises.show', $exercise);
    }

    /**
     * Display the specified resource.
     */
    public function show(Exercise $exercise): Response
    {
        Gate::authorize('view', $exercise);

        return Inertia::render('exercises/show', [
            'exercise' => $this->exerciseData($exercise),
            'canManage' => Gate::allows('update', $exercise),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exercise $exercise): Response
    {
        Gate::authorize('update', $exercise);

        return Inertia::render('exercises/edit', [
            'exercise' => $this->exerciseData($exercise),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $exercise->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise updated.')]);

        return to_route('exercises.show', $exercise);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        Gate::authorize('delete', $exercise);

        $exercise->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise deleted.')]);

        return to_route('exercises.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function exerciseData(Exercise $exercise): array
    {
        return $exercise->only([
            'id',
            'user_id',
            'name',
            'description',
            'goal',
            'difficulty',
            'equipment',
            'duration_minutes',
            'contraindications',
            'age_min',
            'age_max',
            'tags',
            'is_system',
        ]);
    }
}
