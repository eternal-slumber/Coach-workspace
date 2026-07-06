import { Head } from '@inertiajs/react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import ExerciseForm from '@/components/exercise-form';
import Heading from '@/components/heading';
import { Card, CardContent } from '@/components/ui/card';
import { create, index } from '@/routes/exercises';

export default function ExercisesCreate() {
    return (
        <>
            <Head title="Новое упражнение" />

            <main className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Новое упражнение"
                    description="Добавьте упражнение в свою личную библиотеку"
                />
                <Card className="max-w-3xl">
                    <CardContent>
                        <ExerciseForm
                            form={ExerciseController.store.form()}
                            submitLabel="Создать упражнение"
                        />
                    </CardContent>
                </Card>
            </main>
        </>
    );
}

ExercisesCreate.layout = {
    breadcrumbs: [
        { title: 'Упражнения', href: index() },
        { title: 'Новое упражнение', href: create() },
    ],
};
