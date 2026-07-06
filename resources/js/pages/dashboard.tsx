import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import ScheduledTrainingColorDot from '@/components/scheduled-training-color-dot';
import ScheduledTrainingStatusBadge from '@/components/scheduled-training-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { create as createScheduledTraining } from '@/routes/scheduled-trainings';
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
>;

type DashboardProps = {
    scheduledTrainings: DashboardScheduledTraining[];
};

const timeFormatter = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
});

function formatTime(dateTime: string): string {
    return timeFormatter.format(new Date(dateTime));
}

export default function Dashboard({ scheduledTrainings }: DashboardProps) {
    return (
        <>
            <Head title="Сегодня" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div className="grid gap-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Сегодня
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Запланированные тренировки на текущий день
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={createScheduledTraining()}>
                            <Plus />
                            Добавить тренировку
                        </Link>
                    </Button>
                </header>

                {scheduledTrainings.length === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            Сегодня тренировок нет
                        </CardContent>
                    </Card>
                ) : (
                    <section
                        className="grid gap-3"
                        aria-label="Тренировки на сегодня"
                    >
                        {scheduledTrainings.map((scheduledTraining) => (
                            <Card
                                key={scheduledTraining.id}
                                className="gap-0 py-0"
                            >
                                <CardContent className="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex min-w-0 gap-4">
                                        <div className="shrink-0 font-medium tabular-nums">
                                            {formatTime(
                                                scheduledTraining.starts_at,
                                            )}{' '}
                                            —{' '}
                                            {formatTime(
                                                scheduledTraining.ends_at,
                                            )}
                                        </div>

                                        <div className="min-w-0">
                                            <h2 className="flex items-center gap-2 truncate font-medium">
                                                <ScheduledTrainingColorDot
                                                    color={
                                                        scheduledTraining.color
                                                    }
                                                />
                                                <span className="truncate">
                                                    {
                                                        scheduledTraining.subject_name
                                                    }
                                                </span>
                                            </h2>
                                            <p className="text-sm text-muted-foreground">
                                                {scheduledTraining.subject_type ===
                                                'trainee'
                                                    ? 'Клиент'
                                                    : 'Группа'}{' '}
                                                · {scheduledTraining.location}
                                            </p>
                                        </div>
                                    </div>

                                    <ScheduledTrainingStatusBadge
                                        status={scheduledTraining.status}
                                    />
                                </CardContent>
                            </Card>
                        ))}
                    </section>
                )}
            </main>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Сегодня',
            href: dashboard(),
        },
    ],
};
