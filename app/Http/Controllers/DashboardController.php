<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTraining;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * @phpstan-type DashboardItem array{
 *     id: int,
 *     starts_at: string,
 *     ends_at: string,
 *     subject_name: string,
 *     subject_type: string,
 *     location: string,
 *     status: string,
 *     color: string,
 *     training_plan: array{id: int, title: string, status: string}|null
 * }
 */
class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ScheduledTraining::class);

        $today = Date::now()->startOfDay();
        $endDate = $today->copy()->addDays(6)->endOfDay();
        $scheduledTrainings = $request->user()
            ->scheduledTrainings()
            ->select([
                'id',
                'trainee_id',
                'training_group_id',
                'starts_at',
                'ends_at',
                'location',
                'status',
                'color',
            ])
            ->with([
                'trainee:id,name',
                'trainingGroup:id,name',
                'trainingPlan:id,scheduled_training_id,title,status',
            ])
            ->whereBetween('starts_at', [$today, $endDate])
            ->orderBy('starts_at')
            ->get()
            ->map($this->toDashboardItem(...));

        return Inertia::render('dashboard', [
            'days' => $this->days($today, $scheduledTrainings),
            'scheduledTrainings' => $scheduledTrainings,
        ]);
    }

    /**
     * @param  Collection<int, covariant DashboardItem>  $scheduledTrainings
     * @return array<int, array{
     *     date: string,
     *     title: string,
     *     scheduled_trainings: list<DashboardItem>
     * }>
     */
    private function days(CarbonInterface $startDate, Collection $scheduledTrainings): array
    {
        $scheduledTrainingsByDate = $scheduledTrainings->groupBy(
            fn (array $scheduledTraining): string => Date::parse($scheduledTraining['starts_at'])
                ->toDateString(),
        );

        return collect(range(0, 6))
            ->map(function (int $offset) use ($startDate, $scheduledTrainingsByDate): array {
                $date = $startDate->copy()->addDays($offset);
                $dateKey = $date->toDateString();

                return [
                    'date' => $dateKey,
                    'title' => $this->dayTitle($date, $offset),
                    'scheduled_trainings' => array_values($scheduledTrainingsByDate
                        ->get($dateKey, collect())
                        ->values()
                        ->all()),
                ];
            })
            ->all();
    }

    private function dayTitle(CarbonInterface $date, int $offset): string
    {
        return match ($offset) {
            0 => 'Сегодня, '.$this->formatDayAndMonth($date),
            1 => 'Завтра, '.$this->formatDayAndMonth($date),
            default => $this->formatDayAndMonth($date),
        };
    }

    private function formatDayAndMonth(CarbonInterface $date): string
    {
        $months = [
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря',
        ];

        return $date->day.' '.$months[$date->month];
    }

    /** @return DashboardItem */
    private function toDashboardItem(ScheduledTraining $scheduledTraining): array
    {
        $isTraineeTraining = $scheduledTraining->trainee_id !== null;
        $subjectName = $isTraineeTraining
            ? $scheduledTraining->trainee->name
            : $scheduledTraining->trainingGroup->name;

        return [
            'id' => $scheduledTraining->id,
            'starts_at' => $scheduledTraining->starts_at->toIso8601String(),
            'ends_at' => $scheduledTraining->ends_at->toIso8601String(),
            'subject_name' => $subjectName,
            'subject_type' => $isTraineeTraining ? 'trainee' : 'training_group',
            'location' => $scheduledTraining->location,
            'status' => $scheduledTraining->status,
            'color' => $scheduledTraining->color,
            'training_plan' => $scheduledTraining->relationLoaded('trainingPlan')
                && $scheduledTraining->trainingPlan !== null
                    ? [
                        'id' => $scheduledTraining->trainingPlan->id,
                        'title' => $scheduledTraining->trainingPlan->title,
                        'status' => $scheduledTraining->trainingPlan->status,
                    ]
                    : null,
        ];
    }
}
