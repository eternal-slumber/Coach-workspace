<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentMemoryRequest;
use App\Http\Requests\UpdateAgentMemoryRequest;
use App\Models\AgentMemory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AgentMemoryController extends Controller
{
    public function store(StoreAgentMemoryRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $agentMemory = new AgentMemory([
            'type' => $attributes['type'],
            'content' => $attributes['content'],
            'importance' => $attributes['importance'],
            'is_active' => $attributes['is_active'],
        ]);
        $agentMemory->forceFill([
            'trainee_id' => $attributes['trainee_id'] ?? null,
            'training_group_id' => $attributes['training_group_id'] ?? null,
        ]);

        $request->user()->agentMemories()->save($agentMemory);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Memory created.')]);

        return $this->redirectToSubject($agentMemory);
    }

    public function update(
        UpdateAgentMemoryRequest $request,
        AgentMemory $agentMemory,
    ): RedirectResponse {
        $agentMemory->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Memory updated.')]);

        return $this->redirectToSubject($agentMemory);
    }

    private function redirectToSubject(AgentMemory $agentMemory): RedirectResponse
    {
        return $agentMemory->trainee_id !== null
            ? to_route('trainees.show', $agentMemory->trainee_id)
            : to_route('training-groups.show', $agentMemory->training_group_id);
    }
}
