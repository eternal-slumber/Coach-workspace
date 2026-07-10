<?php

namespace App\Console\Commands;

use App\Models\ScheduledTraining;
use App\Models\User;
use App\Services\Agent\TrainingContextBuilder;
use App\Services\Agent\TrainingPromptBuilder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:debug-prompt
    {scheduledTrainingId : ID запланированной тренировки}
    {--user= : ID пользователя, если нужно явно указать}')]
#[Description('Show AI prompt for scheduled training without sending it to model')]
class DebugTrainingPromptCommand extends Command
{
    public function handle(
        TrainingContextBuilder $contextBuilder,
        TrainingPromptBuilder $promptBuilder,
    ): int {
        $scheduledTrainingId = $this->positiveInteger($this->argument('scheduledTrainingId'));

        if ($scheduledTrainingId === null) {
            $this->components->error('Scheduled training ID must be a positive integer.');

            return self::FAILURE;
        }

        $scheduledTraining = ScheduledTraining::query()->find($scheduledTrainingId);

        if ($scheduledTraining === null) {
            $this->components->error("ScheduledTraining #{$scheduledTrainingId} not found.");

            return self::FAILURE;
        }

        $user = $this->resolveUser($scheduledTraining);

        if ($user === null) {
            return self::FAILURE;
        }

        if ($scheduledTraining->user_id !== $user->id) {
            $this->components->error('ScheduledTraining does not belong to this user.');

            return self::FAILURE;
        }

        $context = $contextBuilder->buildForScheduledTraining($user, $scheduledTraining);
        $messages = $promptBuilder->buildTrainingPlanPrompt($context);

        $this->newLine();
        $this->components->info('AI DEBUG PROMPT');
        $this->line(str_repeat('=', 80));
        $this->line('Scheduled training ID: '.$scheduledTraining->id);
        $this->line('User ID: '.$user->id);
        $this->line('AI provider: '.config('ai.provider'));
        $this->line('AI model: '.config('ai.model'));
        $this->newLine();

        foreach ($messages as $index => $message) {
            $this->line(str_repeat('-', 80));
            $this->components->info('MESSAGE #'.($index + 1).' — '.strtoupper($message->role));
            $this->line(str_repeat('-', 80));
            $this->line($message->content);
            $this->newLine();
        }

        $this->line(str_repeat('=', 80));
        $this->components->info('Prompt was not sent to AI model.');

        return self::SUCCESS;
    }

    private function resolveUser(ScheduledTraining $scheduledTraining): ?User
    {
        $userOption = $this->option('user');

        if ($userOption === null) {
            return User::query()->find($scheduledTraining->user_id);
        }

        $userId = $this->positiveInteger($userOption);

        if ($userId === null) {
            $this->components->error('User ID must be a positive integer.');

            return null;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            $this->components->error("User #{$userId} not found.");
        }

        return $user;
    }

    private function positiveInteger(mixed $value): ?int
    {
        $validatedValue = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $validatedValue === false ? null : $validatedValue;
    }
}
