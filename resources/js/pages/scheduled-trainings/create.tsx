import { Head } from '@inertiajs/react';
import ScheduledTrainingController from '@/actions/App/Http/Controllers/ScheduledTrainingController';
import Heading from '@/components/heading';
import ScheduledTrainingForm from '@/components/scheduled-training-form';
import { Card, CardContent } from '@/components/ui/card';
import { create, index } from '@/routes/scheduled-trainings';
import type { SelectionOption } from '@/types';

type ScheduledTrainingsCreateProps = {
    trainees: SelectionOption[];
    trainingGroups: SelectionOption[];
};

export default function ScheduledTrainingsCreate({
    trainees,
    trainingGroups,
}: ScheduledTrainingsCreateProps) {
    return (
        <>
            <Head title="Новая тренировка" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Новая тренировка"
                    description="Укажите время, участника и место проведения"
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <ScheduledTrainingForm
                            form={ScheduledTrainingController.store.form()}
                            submitLabel="Добавить тренировку"
                            trainees={trainees}
                            trainingGroups={trainingGroups}
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

ScheduledTrainingsCreate.layout = {
    breadcrumbs: [
        { title: 'Расписание', href: index() },
        { title: 'Новая тренировка', href: create() },
    ],
};
