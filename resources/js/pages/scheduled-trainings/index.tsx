import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import ScheduledTrainingColorDot from '@/components/scheduled-training-color-dot';
import ScheduledTrainingStatusBadge from '@/components/scheduled-training-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { create, index, show } from '@/routes/scheduled-trainings';
import type { ScheduledTraining } from '@/types';

type ScheduledTrainingsIndexProps = {
    scheduledTrainings: ScheduledTraining[];
};

const dateFormatter = new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
});

const timeFormatter = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
});

export default function ScheduledTrainingsIndex({
    scheduledTrainings,
}: ScheduledTrainingsIndexProps) {
    return (
        <>
            <Head title="Расписание" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Расписание"
                        description="Ближайшие запланированные тренировки"
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Добавить тренировку
                        </Link>
                    </Button>
                </div>

                {scheduledTrainings.length === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            Предстоящих тренировок нет
                        </CardContent>
                    </Card>
                ) : (
                    <section
                        className="grid gap-3"
                        aria-label="Ближайшие тренировки"
                    >
                        {scheduledTrainings.map((scheduledTraining) => (
                            <Card
                                key={scheduledTraining.id}
                                className="gap-0 py-0"
                            >
                                <CardContent className="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="grid min-w-0 gap-1">
                                        <p className="text-sm font-medium capitalize">
                                            {dateFormatter.format(
                                                new Date(
                                                    scheduledTraining.starts_at,
                                                ),
                                            )}
                                        </p>
                                        <Link
                                            href={show(scheduledTraining)}
                                            className="truncate font-semibold hover:underline"
                                        >
                                            <span className="flex items-center gap-2">
                                                <ScheduledTrainingColorDot
                                                    color={
                                                        scheduledTraining.color
                                                    }
                                                />
                                                <span className="truncate">
                                                    {timeFormatter.format(
                                                        new Date(
                                                            scheduledTraining.starts_at,
                                                        ),
                                                    )}
                                                    –
                                                    {timeFormatter.format(
                                                        new Date(
                                                            scheduledTraining.ends_at,
                                                        ),
                                                    )}{' '}
                                                    —{' '}
                                                    {
                                                        scheduledTraining.subject_name
                                                    }
                                                </span>
                                            </span>
                                        </Link>
                                        <p className="text-sm text-muted-foreground">
                                            {scheduledTraining.location}
                                        </p>
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

ScheduledTrainingsIndex.layout = {
    breadcrumbs: [{ title: 'Расписание', href: index() }],
};
