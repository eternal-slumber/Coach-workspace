import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import TrainingPlanForm from '@/components/training-plan-form';
import { index } from '@/routes/training-plans';
import type { ExercisePlanOption, TrainingPlan } from '@/types';

type TrainingPlansEditProps = {
    trainingPlan: TrainingPlan;
    exercises: ExercisePlanOption[];
};

export default function TrainingPlansEdit({
    trainingPlan,
    exercises,
}: TrainingPlansEditProps) {
    return (
        <>
            <Head title={`Редактирование — ${trainingPlan.title}`} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Редактирование плана"
                    description={trainingPlan.title}
                />
                <TrainingPlanForm
                    scheduledTrainings={[]}
                    exercises={exercises}
                    trainingPlan={trainingPlan}
                />
            </main>
        </>
    );
}

TrainingPlansEdit.layout = {
    breadcrumbs: [{ title: 'Планы тренировок', href: index() }],
};
