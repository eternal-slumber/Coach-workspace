import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import ScheduledTrainingController from '@/actions/App/Http/Controllers/ScheduledTrainingController';
import ResourceDeleteDialog from '@/components/resource-delete-dialog';
import ScheduledTrainingColorDot from '@/components/scheduled-training-color-dot';
import ScheduledTrainingStatusBadge from '@/components/scheduled-training-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { getScheduledTrainingColor } from '@/lib/scheduled-training-colors';
import { edit, index } from '@/routes/scheduled-trainings';
import type { ScheduledTraining } from '@/types';

type ScheduledTrainingsShowProps = {
    scheduledTraining: ScheduledTraining;
};

const dateTimeFormatter = new Intl.DateTimeFormat('ru-RU', {
    dateStyle: 'long',
    timeStyle: 'short',
});

export default function ScheduledTrainingsShow({
    scheduledTraining,
}: ScheduledTrainingsShowProps) {
    const colorOption = getScheduledTrainingColor(scheduledTraining.color);

    return (
        <>
            <Head title={scheduledTraining.subject_name} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="grid gap-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {scheduledTraining.subject_name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {scheduledTraining.subject_type === 'trainee'
                                ? 'Индивидуальная тренировка'
                                : 'Групповая тренировка'}
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-3">
                        <Button variant="outline" asChild>
                            <Link href={edit(scheduledTraining)}>
                                <Pencil />
                                Редактировать
                            </Link>
                        </Button>
                        <ResourceDeleteDialog
                            form={ScheduledTrainingController.destroy.form(
                                scheduledTraining,
                            )}
                            resourceName={scheduledTraining.subject_name}
                        />
                    </div>
                </div>

                <Card className="max-w-3xl">
                    <CardContent>
                        <dl className="grid gap-6 sm:grid-cols-2">
                            <div className="grid gap-1 sm:col-span-2">
                                <dt className="text-sm text-muted-foreground">
                                    Время
                                </dt>
                                <dd>
                                    {dateTimeFormatter.format(
                                        new Date(scheduledTraining.starts_at),
                                    )}{' '}
                                    —{' '}
                                    {dateTimeFormatter.format(
                                        new Date(scheduledTraining.ends_at),
                                    )}
                                </dd>
                            </div>
                            <div className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    Локация
                                </dt>
                                <dd>{scheduledTraining.location}</dd>
                            </div>
                            <div className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    Цвет в календаре
                                </dt>
                                <dd className="flex items-center gap-2">
                                    <ScheduledTrainingColorDot
                                        color={scheduledTraining.color}
                                    />
                                    {colorOption.label}
                                </dd>
                            </div>
                            <div className="grid gap-1">
                                <dt className="text-sm text-muted-foreground">
                                    Статус
                                </dt>
                                <dd>
                                    <ScheduledTrainingStatusBadge
                                        status={scheduledTraining.status}
                                    />
                                </dd>
                            </div>
                            <div className="grid gap-1 sm:col-span-2">
                                <dt className="text-sm text-muted-foreground">
                                    Заметка
                                </dt>
                                <dd className="whitespace-pre-wrap">
                                    {scheduledTraining.notes || 'Не указано'}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

ScheduledTrainingsShow.layout = {
    breadcrumbs: [{ title: 'Расписание', href: index() }],
};
