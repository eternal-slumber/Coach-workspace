import { Head } from '@inertiajs/react';
import TraineeController from '@/actions/App/Http/Controllers/TraineeController';
import Heading from '@/components/heading';
import TraineeForm from '@/components/trainee-form';
import { Card, CardContent } from '@/components/ui/card';
import { index } from '@/routes/trainees';
import type { Trainee } from '@/types';

type TraineesEditProps = {
    trainee: Trainee;
};

export default function TraineesEdit({ trainee }: TraineesEditProps) {
    return (
        <>
            <Head title={`Редактирование — ${trainee.name}`} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Редактирование клиента"
                    description={trainee.name}
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <TraineeForm
                            form={TraineeController.update.form.patch(trainee)}
                            submitLabel="Сохранить изменения"
                            trainee={trainee}
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

TraineesEdit.layout = {
    breadcrumbs: [{ title: 'Клиенты', href: index() }],
};
