<?php

namespace App\Policies;

use App\Models\TrainingNote;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TrainingNotePolicy
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
    public function view(User $user, TrainingNote $trainingNote): Response
    {
        return $this->owns($user, $trainingNote);
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
    public function update(User $user, TrainingNote $trainingNote): Response
    {
        return $this->owns($user, $trainingNote);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrainingNote $trainingNote): Response
    {
        return $this->owns($user, $trainingNote);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TrainingNote $trainingNote): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TrainingNote $trainingNote): bool
    {
        return false;
    }

    private function owns(User $user, TrainingNote $trainingNote): Response
    {
        return $trainingNote->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
