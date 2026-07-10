<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTraining;
use App\Services\Agent\InvalidTrainingPlanResponseException;
use App\Services\Agent\TrainingAgentService;
use App\Services\Agent\TrainingPlanAlreadyExistsException;
use App\Services\AI\AiClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Throwable;

class GenerateTrainingPlanController extends Controller
{
    public function __invoke(
        Request $request,
        ScheduledTraining $scheduledTraining,
        TrainingAgentService $trainingAgent,
    ): RedirectResponse {
        Gate::authorize('generateTrainingPlan', $scheduledTraining);

        try {
            $trainingPlan = $trainingAgent->generatePlanForScheduledTraining(
                $request->user(),
                $scheduledTraining,
            );
        } catch (TrainingPlanAlreadyExistsException $exception) {
            return $this->failure($exception, 'warning');
        } catch (AiClientException|InvalidTrainingPlanResponseException $exception) {
            report($exception);

            return $this->failure(
                $exception,
                'error',
                'Не удалось сгенерировать корректный план. Попробуйте ещё раз.',
            );
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'AI-черновик плана создан.',
        ]);

        return to_route('training-plans.show', $trainingPlan);
    }

    private function failure(
        Throwable $exception,
        string $type,
        ?string $message = null,
    ): RedirectResponse {
        Inertia::flash('toast', [
            'type' => $type,
            'message' => $message ?? $exception->getMessage(),
        ]);

        return back();
    }
}
