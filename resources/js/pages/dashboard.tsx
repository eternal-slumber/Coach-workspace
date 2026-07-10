import { Head, Link } from '@inertiajs/react';
import { Bot, CalendarPlus, ExternalLink, Plus } from 'lucide-react';
import ScheduledTrainingColorDot from '@/components/scheduled-training-color-dot';
import ScheduledTrainingStatusBadge from '@/components/scheduled-training-status-badge';
import TrainingPlanStatusBadge from '@/components/training-plan-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import {
    create as createScheduledTraining,
    generateTrainingPlan,
    show as showScheduledTraining,
} from '@/routes/scheduled-trainings';
import { show as showTrainingPlan } from '@/routes/training-plans';
import type { ScheduledTraining } from '@/types';

type DashboardScheduledTraining = Pick<
    ScheduledTraining,
    | 'id'
    | 'starts_at'
    | 'ends_at'
    | 'subject_name'
    | 'subject_type'
    | 'location'
    | 'status'
    | 'color'
    | 'training_plan'
>;

type DashboardDay = {
    date: string;
    title: string;
    scheduled_trainings: DashboardScheduledTraining[];
};

type DashboardProps = {
    days: DashboardDay[];
    scheduledTrainings: DashboardScheduledTraining[];
};

const timeFormatter = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
});

function formatTime(dateTime: string): string {
    return timeFormatter.format(new Date(dateTime));
}

function subjectTypeLabel(subjectType: DashboardScheduledTraining['subject_type']) {
    return subjectType === 'trainee' ? 'Клиент' : 'Группа';
}

export default function Dashboard({ days, scheduledTrainings }: DashboardProps) {
    const today = days[0];
    const upcomingDays = days.slice(1);

    return (
        <>
            <Head title="Обзор" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div className="grid gap-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Обзор
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Ближайшие тренировки и быстрые действия
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={createScheduledTraining()}>
                            <Plus />
                            Добавить тренировку
                        </Link>
                    </Button>
                </header>

                <section className="grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
                    <TrainingDayCard
                        title={today?.title ?? 'Сегодня'}
                        emptyText="Сегодня тренировок нет"
                        scheduledTrainings={today?.scheduled_trainings ?? []}
                        primary
                    />

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                Быстрый статус
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 text-sm">
                            <div className="flex items-center justify-between gap-3 rounded-lg border p-3">
                                <span className="text-muted-foreground">
                                    Ближайшие тренировки
                                </span>
                                <span className="font-medium">
                                    {scheduledTrainings.length}
                                </span>
                            </div>
                            <div className="flex items-center justify-between gap-3 rounded-lg border p-3">
                                <span className="text-muted-foreground">
                                    Без плана
                                </span>
                                <span className="font-medium">
                                    {
                                        scheduledTrainings.filter(
                                            (scheduledTraining) =>
                                                scheduledTraining.training_plan ===
                                                null,
                                        ).length
                                    }
                                </span>
                            </div>
                            <Button asChild variant="outline">
                                <Link href={createScheduledTraining()}>
                                    <CalendarPlus />
                                    Запланировать занятие
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4" aria-label="Ближайшие дни">
                    <h2 className="text-lg font-semibold">Ближайшие дни</h2>
                    <div className="grid gap-4">
                        {upcomingDays.map((day) => (
                            <TrainingDayCard
                                key={day.date}
                                title={day.title}
                                emptyText="Нет тренировок"
                                scheduledTrainings={day.scheduled_trainings}
                            />
                        ))}
                    </div>
                </section>
            </main>
        </>
    );
}

function TrainingDayCard({
    title,
    emptyText,
    scheduledTrainings,
    primary = false,
}: {
    title: string;
    emptyText: string;
    scheduledTrainings: DashboardScheduledTraining[];
    primary?: boolean;
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className={primary ? 'text-lg' : 'text-base'}>
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {scheduledTrainings.length === 0 ? (
                    <div className="rounded-lg border border-dashed py-8 text-center text-sm text-muted-foreground">
                        {emptyText}
                    </div>
                ) : (
                    <div className="grid gap-3">
                        {scheduledTrainings.map((scheduledTraining) => (
                            <TrainingRow
                                key={scheduledTraining.id}
                                scheduledTraining={scheduledTraining}
                            />
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function TrainingRow({
    scheduledTraining,
}: {
    scheduledTraining: DashboardScheduledTraining;
}) {
    return (
        <article className="flex flex-col gap-4 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex min-w-0 gap-4">
                <div className="shrink-0 font-medium tabular-nums">
                    {formatTime(scheduledTraining.starts_at)}–
                    {formatTime(scheduledTraining.ends_at)}
                </div>

                <div className="min-w-0">
                    <h3 className="flex items-center gap-2 font-medium">
                        <ScheduledTrainingColorDot
                            color={scheduledTraining.color}
                        />
                        <span className="truncate">
                            {scheduledTraining.subject_name}
                        </span>
                    </h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {subjectTypeLabel(scheduledTraining.subject_type)} ·{' '}
                        {scheduledTraining.location}
                    </p>
                    <div className="mt-2 flex flex-wrap items-center gap-2">
                        <ScheduledTrainingStatusBadge
                            status={scheduledTraining.status}
                        />
                        {scheduledTraining.training_plan === null ? (
                            <span className="rounded-md border px-2 py-0.5 text-xs text-muted-foreground">
                                Нет плана
                            </span>
                        ) : (
                            <TrainingPlanStatusBadge
                                status={scheduledTraining.training_plan.status}
                            />
                        )}
                    </div>
                </div>
            </div>

            <div className="flex flex-wrap gap-2 sm:justify-end">
                <Button asChild size="sm" variant="outline">
                    <Link href={showScheduledTraining(scheduledTraining)}>
                        <ExternalLink />
                        Открыть
                    </Link>
                </Button>

                {scheduledTraining.training_plan === null ? (
                    <Button asChild size="sm">
                        <Link
                            href={generateTrainingPlan(scheduledTraining)}
                            method="post"
                            as="button"
                        >
                            <Bot />
                            AI-план
                        </Link>
                    </Button>
                ) : (
                    <Button asChild size="sm" variant="secondary">
                        <Link
                            href={showTrainingPlan(
                                scheduledTraining.training_plan,
                            )}
                        >
                            План
                        </Link>
                    </Button>
                )}
            </div>
        </article>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Обзор',
            href: dashboard(),
        },
    ],
};
