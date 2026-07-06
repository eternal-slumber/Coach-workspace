import { Head } from '@inertiajs/react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import ExerciseForm from '@/components/exercise-form';
import Heading from '@/components/heading';
import { Card, CardContent } from '@/components/ui/card';
import { index } from '@/routes/exercises';
import type { Exercise } from '@/types';

type ExercisesEditProps = {
    exercise: Exercise;
};

export default function ExercisesEdit({ exercise }: ExercisesEditProps) {
    return (
        <>
            <Head title={`Редактирование — ${exercise.name}`} />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Редактирование упражнения"
                    description={exercise.name}
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <ExerciseForm
                            form={ExerciseController.update.form.patch(
                                exercise,
                            )}
                            submitLabel="Сохранить изменения"
                            exercise={exercise}
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

ExercisesEdit.layout = {
    breadcrumbs: [{ title: 'Упражнения', href: index() }],
};
