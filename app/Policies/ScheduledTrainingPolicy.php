<?php

namespace App\Policies;

use App\Models\ScheduledTraining;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScheduledTrainingPolicy
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
    public function view(User $user, ScheduledTraining $scheduledTraining): Response
    {
        return $this->owns($user, $scheduledTraining);
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
    public function update(User $user, ScheduledTraining $scheduledTraining): Response
    {
        return $this->owns($user, $scheduledTraining);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduledTraining $scheduledTraining): Response
    {
        return $this->owns($user, $scheduledTraining);
    }

    public function duplicate(User $user, ScheduledTraining $scheduledTraining): Response
    {
        return $this->owns($user, $scheduledTraining);
    }

    private function owns(User $user, ScheduledTraining $scheduledTraining): Response
    {
        return $scheduledTraining->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
