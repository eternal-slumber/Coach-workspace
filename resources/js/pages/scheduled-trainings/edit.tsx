import { Head } from '@inertiajs/react';
import ScheduledTrainingController from '@/actions/App/Http/Controllers/ScheduledTrainingController';
import Heading from '@/components/heading';
import ScheduledTrainingForm from '@/components/scheduled-training-form';
import { Card, CardContent } from '@/components/ui/card';
import { index } from '@/routes/scheduled-trainings';
import type { ScheduledTraining, SelectionOption } from '@/types';

type ScheduledTrainingsEditProps = {
    scheduledTraining: ScheduledTraining;
    trainees: SelectionOption[];
    trainingGroups: SelectionOption[];
};

export default function ScheduledTrainingsEdit({
    scheduledTraining,
    trainees,
    trainingGroups,
}: ScheduledTrainingsEditProps) {
    return (
        <>
            <Head
                title={`Редактирование — ${scheduledTraining.subject_name}`}
            />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Редактирование тренировки"
                    description={scheduledTraining.subject_name}
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <ScheduledTrainingForm
                            form={ScheduledTrainingController.update.form.patch(
                                scheduledTraining,
                            )}
                            submitLabel="Сохранить изменения"
                            trainees={trainees}
                            trainingGroups={trainingGroups}
                            scheduledTraining={scheduledTraining}
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

ScheduledTrainingsEdit.layout = {
    breadcrumbs: [{ title: 'Расписание', href: index() }],
};
