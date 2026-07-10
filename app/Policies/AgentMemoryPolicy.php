<?php

namespace App\Policies;

use App\Models\AgentMemory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgentMemoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AgentMemory $agentMemory): Response
    {
        return $this->owns($user, $agentMemory);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AgentMemory $agentMemory): Response
    {
        return $this->owns($user, $agentMemory);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AgentMemory $agentMemory): Response
    {
        return $this->owns($user, $agentMemory);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AgentMemory $agentMemory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AgentMemory $agentMemory): bool
    {
        return false;
    }

    private function owns(User $user, AgentMemory $agentMemory): Response
    {
        return $agentMemory->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
