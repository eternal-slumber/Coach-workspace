import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import TrainingPlanStatusBadge from '@/components/training-plan-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { create, index, show } from '@/routes/training-plans';
import type { TrainingPlanListItem } from '@/types';

type TrainingPlansIndexProps = {
    trainingPlans: TrainingPlanListItem[];
};

const dateFormatter = new Intl.DateTimeFormat('ru-RU', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

export default function TrainingPlansIndex({
    trainingPlans,
}: TrainingPlansIndexProps) {
    return (
        <>
            <Head title="Планы тренировок" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Планы тренировок"
                        description="Ручные планы для запланированных занятий"
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Создать план
                        </Link>
                    </Button>
                </div>

                {trainingPlans.length === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            Планов тренировок пока нет
                        </CardContent>
                    </Card>
                ) : (
                    <section
                        className="grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                        aria-label="Список планов тренировок"
                    >
                        {trainingPlans.map((trainingPlan) => (
                            <Card key={trainingPlan.id} className="gap-3">
                                <CardContent className="grid gap-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="grid gap-1">
                                            <Link
                                                href={show(trainingPlan)}
                                                className="font-semibold hover:underline"
                                            >
                                                {trainingPlan.title}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">
                                                {trainingPlan.subject_name}
                                            </p>
                                        </div>
                                        <TrainingPlanStatusBadge
                                            status={trainingPlan.status}
                                        />
                                    </div>

                                    <p className="line-clamp-2 text-sm">
                                        {trainingPlan.goal}
                                    </p>

                                    <div className="grid gap-1 text-sm text-muted-foreground">
                                        <p>
                                            {dateFormatter.format(
                                                new Date(
                                                    trainingPlan
                                                        .scheduled_training
                                                        .starts_at,
                                                ),
                                            )}
                                        </p>
                                        <p>
                                            {trainingPlan.blocks_count} блоков ·{' '}
                                            {
                                                trainingPlan.total_duration_minutes
                                            }{' '}
                                            мин.
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </section>
                )}
            </main>
        </>
    );
}

TrainingPlansIndex.layout = {
    breadcrumbs: [{ title: 'Планы тренировок', href: index() }],
};
