import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';

type ScheduledTrainingStatus = 'planned' | 'completed' | 'cancelled';

type ScheduledTraining = {
    id: number;
    starts_at: string;
    ends_at: string;
    subject_name: string;
    subject_type: 'trainee' | 'training_group';
    location: string;
    status: ScheduledTrainingStatus;
};

type DashboardProps = {
    scheduledTrainings: ScheduledTraining[];
};

const timeFormatter = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
});

const statusStyles: Record<ScheduledTrainingStatus, string> = {
    planned:
        'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300',
    completed:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
    cancelled:
        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
};

function formatTime(dateTime: string): string {
    return timeFormatter.format(new Date(dateTime));
}

export default function Dashboard({ scheduledTrainings }: DashboardProps) {
    return (
        <>
            <Head title="Сегодня" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-col gap-1">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Сегодня
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Запланированные тренировки на текущий день
                    </p>
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
                                            <h2 className="truncate font-medium">
                                                {scheduledTraining.subject_name}
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

                                    <Badge
                                        variant="outline"
                                        className={
                                            statusStyles[
                                                scheduledTraining.status
                                            ]
                                        }
                                    >
                                        Статус: {scheduledTraining.status}
                                    </Badge>
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
