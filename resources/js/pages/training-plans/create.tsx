import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import TrainingPlanForm from '@/components/training-plan-form';
import { create, index } from '@/routes/training-plans';
import type { ExercisePlanOption, ScheduledTrainingPlanOption } from '@/types';

type TrainingPlansCreateProps = {
    scheduledTrainings: ScheduledTrainingPlanOption[];
    exercises: ExercisePlanOption[];
    selectedScheduledTrainingId: number | null;
};

export default function TrainingPlansCreate({
    scheduledTrainings,
    exercises,
    selectedScheduledTrainingId,
}: TrainingPlansCreateProps) {
    return (
        <>
            <Head title="Новый план тренировки" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Новый план тренировки"
                    description="Соберите тренировку из последовательных блоков и упражнений"
                />
                <TrainingPlanForm
                    scheduledTrainings={scheduledTrainings}
                    exercises={exercises}
                    selectedScheduledTrainingId={selectedScheduledTrainingId}
                />
            </main>
        </>
    );
}

TrainingPlansCreate.layout = {
    breadcrumbs: [
        { title: 'Планы тренировок', href: index() },
        { title: 'Новый план', href: create() },
    ],
};
